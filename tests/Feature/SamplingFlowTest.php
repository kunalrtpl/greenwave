<?php

namespace Tests\Feature;

use App\Product;
use App\SamplingDispatch;
use App\SamplingRequest;
use App\Services\SamplingRequestService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Walks the revamped sampling module through its two steps over HTTP:
 * request comes in from the API → admin edits / approves / marks ready →
 * dispatch closes it.
 *
 * Runs inside a transaction against the configured database and rolls back.
 */
class SamplingFlowTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $admin;

    /** @var \App\User */
    private $executive;

    /** @var \Illuminate\Support\Collection */
    private $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin     = User::where('type', 'admin')->firstOrFail();
        $this->executive = User::marketingEmployees()->orderBy('users.id')->firstOrFail();
        $this->products  = Product::where('status', 1)->take(3)->get();

        if ($this->products->count() < 3) {
            $this->markTestSkipped('Needs at least three active products.');
        }
    }

    /** One API post with three products becomes three SR-n.m lines. */
    public function testApiCreatesOneLinePerProduct()
    {
        $lines = $this->raiseRequest();

        $this->assertCount(3, $lines);
        $this->assertSame(
            ['.1', '.2', '.3'],
            $lines->map(function ($l) { return substr($l->reference_no, strpos($l->reference_no, '.')); })->all()
        );
        $this->assertSame(1, $lines->pluck('request_group_no')->unique()->count(), 'all lines share one group');
        $this->assertSame('pending', $lines->first()->status);
        $this->assertEquals($this->executive->id, $lines->first()->dispatch_user_id,
            'with no dealer or customer the material goes to the requesting executive');
    }

    /** Each status screen renders and only shows lines of that status. */
    public function testListingScreensRenderPerStatus()
    {
        $lines = $this->raiseRequest();
        $pending = $lines->first();

        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/pending')
            ->assertStatus(200)
            ->assertSee($pending->reference_no);

        // The request has no declined product yet, so it is absent entirely.
        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/declined')
            ->assertStatus(200)
            ->assertDontSee($pending->reference_no);

        // An unknown status falls back to pending rather than erroring.
        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/pending?status=nonsense')
            ->assertStatus(200)
            ->assertSee($pending->reference_no);
    }

    /** The detail screen shows the request and, once approved, is read-only. */
    public function testDetailScreenRenders()
    {
        $line = $this->raiseRequest()->first();

        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $line->id)
            ->assertStatus(200)
            ->assertSee($line->reference_no)
            ->assertSee('Approve')
            ->assertSee('Decline');

        $this->approve($line, 5);

        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $line->id)
            ->assertStatus(200)
            ->assertSee('Mark as Ready');
    }

    /** Admin corrections are applied and written to the audit log. */
    public function testAdminCanEditAPendingRequest()
    {
        $line  = $this->raiseRequest()->first();
        $other = $this->products->last();

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id, [
                'product_id'           => $other->id,
                'approved_pack_size'   => 1,
                'approved_no_of_packs' => 7,
                'change_remarks'       => 'Original product is out of stock',
            ])
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $line = $line->fresh();
        $this->assertEquals($other->id, $line->product_id);
        $this->assertEquals($this->products->first()->id, $line->requested_product_id,
            'what was originally asked for is never overwritten');
        $this->assertEquals(10, $line->requested_qty, 'the requested qty is a record, not something the admin edits');
        $this->assertEquals(7, $line->approved_no_of_packs);

        // The log stores names, not ids — "Product: 4 → 6" tells a reader nothing.
        $log = $line->logs()->where('action', 'updated')->firstOrFail();
        $this->assertArrayHasKey('product_id', $log->changes);
        $this->assertEquals($other->product_name, $log->changes['product_id']['to']);
        $this->assertEquals($this->products->first()->product_name, $log->changes['product_id']['from']);
    }

    /** Approved qty is pack size x no of packs — it is never posted directly. */
    public function testApprovedQtyIsDerivedFromThePacking()
    {
        $line = $this->raiseRequest()->first();   // 10 kg requested

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/approve', [
                'approved_pack_size'   => 1,
                'approved_no_of_packs' => 4,
                'approval_remarks'     => 'Only 4 kg available',
            ])
            ->assertStatus(200);

        $line = $line->fresh();
        $this->assertSame('approved', $line->status);
        $this->assertEquals(4, $line->approved_qty, '1 kg x 4 packs');
        $this->assertEquals(4, $line->pending_qty);
        $this->assertEquals(1, $line->approved_pack_size);
        $this->assertEquals(4, $line->approved_no_of_packs);
        $this->assertEquals($this->admin->id, $line->approved_by_user_id);
    }

    /** A qty posted on its own is ignored; the packing is what counts. */
    public function testApproveRejectsAMissingPacking()
    {
        $line = $this->raiseRequest()->first();

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $line->id . '/approve', ['approved_qty' => 4])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['approved_pack_size', 'approved_no_of_packs']);
    }

    /** A powder can only be packed in 1 kg, so 5 kg has to be refused. */
    public function testPackSizeMustSuitThePhysicalForm()
    {
        $powder = Product::where('status', 1)->where('physical_form', 'Powder')->first();
        if (!$powder) {
            $this->markTestSkipped('Needs an active powder product.');
        }

        $line = $this->raiseRequest()->first();

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $line->id . '/approve', [
                'product_id'           => $powder->id,
                'approved_pack_size'   => 5,
                'approved_no_of_packs' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('approved_pack_size');
    }

    /** The detail screen is one product: its own approve and decline. */
    public function testDetailScreenIsOneProduct()
    {
        $lines = $this->raiseRequest();
        $first = $lines->first();
        $other = $lines->last();

        $response = $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $first->id)
            ->assertStatus(200)
            ->assertSee($first->reference_no);

        // The other products of the same post are separate requests and are
        // not dragged onto this page.
        $response->assertDontSee($other->reference_no);
    }

    /**
     * The approve dropdown opens on the product that was asked for.
     *
     * Without this the dropdown fell back to the first product in the list, so
     * an admin pressing Approve straight away authorised the wrong product.
     */
    public function testApproveFormDefaultsToTheRequestedProduct()
    {
        $line = $this->raiseRequest()->first();

        $html = $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $line->id)
            ->assertStatus(200)
            ->getContent();

        $this->assertSelectedProduct($html, $line->requested_product_id);
    }

    /**
     * Even a product deactivated since the app asked for it: the request keeps
     * pointing at it, flagged inactive, rather than at some other product.
     */
    public function testApproveFormKeepsADeactivatedProductSelected()
    {
        $line    = $this->raiseRequest()->first();
        $product = Product::findOrFail($line->requested_product_id);

        $product->status = 0;
        $product->save();

        $html = $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $line->id)
            ->assertStatus(200)
            ->getContent();

        $select = $this->extractSelect($html, 'srProductSelect');
        $this->assertSelectedProduct($html, $product->id);
        $this->assertContains($product->product_name . ' (' . $product->product_code . ') — Inactive', $select,
            'the admin is told the product is no longer active');
    }

    /** Assert the product dropdown's selected option is $productId. */
    private function assertSelectedProduct($html, $productId)
    {
        $select = $this->extractSelect($html, 'srProductSelect');

        $this->assertRegExp(
            '/<option value="' . $productId . '"[^>]*\bselected\b/',
            $select,
            'the requested product is the selected option'
        );
        $this->assertSame(1, preg_match_all('/\bselected\b/', $select),
            'exactly one option is selected');
    }

    /** @return string the markup of the <select> carrying $id */
    private function extractSelect($html, $id)
    {
        $start = strpos($html, 'id="' . $id . '"');
        $this->assertNotFalse($start, 'the ' . $id . ' dropdown is on the page');

        $end = strpos($html, '</select>', $start);

        return substr($html, $start, $end - $start);
    }

    /**
     * The sidebar badges what is waiting to be done. "Dispatched Samples
     * (All)" is a history screen, so the client asked for no count on it.
     */
    public function testTheDispatchedSamplesMenuCarriesNoCount()
    {
        $lines = $this->raiseRequest();
        $this->approve($lines->first(), 5);
        $this->markReady($lines->first()->fresh());

        // Something IS ready to dispatch, which is what used to be counted.
        $this->assertGreaterThan(0, SamplingRequest::pendingDispatch()->where('is_ready', 1)->count());

        $this->assertSame([], \App\Module::getPendingCounts('samplingDispatch'));

        $html = $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch')
            ->assertStatus(200)
            ->getContent();

        // The menu item is there, with no badge between its name and the
        // closing tag of its link.
        $this->assertContains('Dispatched Samples (All)', $html);
        $start = strpos($html, 'Dispatched Samples (All)');
        $item  = substr($html, $start, strpos($html, '</a>', $start) - $start);
        $this->assertNotContains('badge', $item, 'no count on the dispatch history menu');

        // The pending menu still has its badge, so this did not switch them all off.
        $this->assertArrayHasKey('pending', \App\Module::getPendingCounts('samplingRequests'));
    }

    public function testDeclineNeedsAReason()
    {
        $line = $this->raiseRequest()->first();

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $line->id . '/decline', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('decline_reason');

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/decline', ['decline_reason' => 'Sampled last month'])
            ->assertStatus(200);

        $this->assertSame('declined', $line->fresh()->status);
    }

    /* ───────────── Declining after approval ───────────── */

    /**
     * The admin may change their mind: an approved request can still be
     * declined, and the qty it had reserved goes back to the employee.
     */
    public function testAnApprovedRequestCanStillBeDeclined()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);

        $line  = $line->fresh();
        $stock = \App\FreeSamplingStock::where('user_id', $line->user_id)
            ->where('product_id', $line->product_id)->firstOrFail();
        $reserved = $stock->pending_orders;

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/decline',
                   ['decline_reason' => 'Customer postponed the trial'])
            ->assertStatus(200);

        $line = $line->fresh();
        $this->assertSame('declined', $line->status);
        $this->assertSame('Customer postponed the trial', $line->decline_reason);
        $this->assertNotNull($line->declined_at);

        $this->assertEquals($reserved - 5, $stock->fresh()->pending_orders,
            'the reserved qty is released back to the employee');

        $this->assertSame(1, \App\SamplingRequestLog::where('sampling_request_id', $line->id)
            ->where('action', \App\SamplingRequestLog::ACTION_DECLINED)->count(),
            'and the change is on the timeline');
    }

    /** Even after the stores team has packed it. */
    public function testAReadyRequestCanStillBeDeclined()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);
        $this->markReady($line->fresh());

        $this->assertTrue((bool) $line->fresh()->is_ready);

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/decline',
                   ['decline_reason' => 'Stock needed elsewhere'])
            ->assertStatus(200);

        $line = $line->fresh();
        $this->assertSame('declined', $line->status);
        $this->assertFalse((bool) $line->is_ready, 'it leaves the dispatch desk');
        $this->assertNotNull($line->ready_at, 'but the ready stamp stays as history');

        // And it is gone from the dispatch picker.
        $picker = $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch/requests')
            ->assertStatus(200)
            ->getContent();
        $this->assertNotContains($line->reference_no, $picker);
    }

    /** What a decline cannot undo: material that has already gone out. */
    public function testADispatchedRequestCannotBeDeclined()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);
        $this->markReady($line->fresh());
        $this->dispatchQty($line->fresh(), 2);

        $line = $line->fresh();
        $this->assertSame('approved', $line->status, 'partly dispatched, so still approved');

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $line->id . '/decline',
                       ['decline_reason' => 'Changed my mind'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame('approved', $line->fresh()->status, 'the earlier state is left alone');
    }

    /** A fully dispatched (closed) request cannot be declined either. */
    public function testAClosedRequestCannotBeDeclined()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);
        $this->markReady($line->fresh());
        $this->dispatchQty($line->fresh(), 5);

        $this->assertSame('closed', $line->fresh()->status);

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $line->id . '/decline',
                       ['decline_reason' => 'Too late'])
            ->assertStatus(422);

        $this->assertSame('closed', $line->fresh()->status);
    }

    public function testDecliningTwiceIsRefused()
    {
        $line = $this->raiseRequest()->first();

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/decline', ['decline_reason' => 'No'])
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $line->id . '/decline', ['decline_reason' => 'No again'])
            ->assertStatus(422);
    }

    /** The button is on the screen at every stage it is allowed. */
    public function testTheDeclineButtonIsOfferedUntilSomethingIsDispatched()
    {
        $line = $this->raiseRequest()->first();

        // The button itself, not the handler that is always in the script.
        $button = 'id="srDeclineBtn"';

        $this->assertContains($button, $this->detailScreen($line), 'while pending');

        $this->approve($line, 5);
        $this->assertContains($button, $this->detailScreen($line), 'once approved');

        $this->markReady($line->fresh());
        $html = $this->detailScreen($line);
        $this->assertContains($button, $html, 'and once marked ready');
        $this->assertContains('Decline', $html);

        $this->dispatchQty($line->fresh(), 5);
        $this->assertNotContains($button, $this->detailScreen($line),
            'but not once the material has gone out');
    }

    /** Send $qty of one line out on a challan of its own. */
    private function dispatchQty(SamplingRequest $line, $qty)
    {
        $this->dispatch([['sampling_request_id' => $line->id, 'dispatch_qty' => $qty, 'batch_no' => 'B-D1']]);
    }

    /** @return string the rendered detail page */
    private function detailScreen(SamplingRequest $line)
    {
        return $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $line->id)
            ->assertStatus(200)
            ->getContent();
    }

    /** A line has to be approved and marked ready before it can go out. */
    public function testDispatchRequiresAnApprovedAndReadyLine()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challan([
                ['sampling_request_id' => $line->id, 'dispatch_qty' => 2],
            ]))
            ->assertStatus(422);

        $this->markReady($line);

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challan([
                ['sampling_request_id' => $line->id, 'dispatch_qty' => 2],
            ]))
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertEquals(2, $line->fresh()->total_dispatched_qty);
    }

    /** Partial dispatch keeps the line open; the last one closes it. */
    public function testPartialThenFullDispatchClosesTheLine()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);
        $this->markReady($line);

        $this->dispatch([['sampling_request_id' => $line->id, 'dispatch_qty' => 3, 'batch_no' => 'B-1']]);

        $line = $line->fresh();
        $this->assertSame('approved', $line->status, 'still open with qty pending');
        $this->assertEquals(2, $line->pending_qty);

        $this->dispatch([['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_no' => 'B-2']]);

        $line = $line->fresh();
        $this->assertSame('closed', $line->status);
        $this->assertEquals(0, $line->pending_qty);
        $this->assertNotNull($line->closed_at);
        $this->assertSame(2, SamplingDispatch::where('sampling_request_id', $line->id)->count());
    }

    public function testCannotDispatchMoreThanPending()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 5);
        $this->markReady($line);

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challan([
                ['sampling_request_id' => $line->id, 'dispatch_qty' => 6],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.dispatch_qty');
    }

    /** Several requests on one challan share the challan but keep their own party. */
    public function testMultipleRequestsOnOneChallan()
    {
        $lines = $this->raiseRequest();
        $a = $lines[0];
        $b = $lines[1];

        foreach ([$a, $b] as $line) {
            $this->approve($line, 2);
            $this->markReady($line);
        }

        $this->dispatch([
            ['sampling_request_id' => $a->id, 'dispatch_qty' => 2, 'batch_no' => 'BA',
             'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id],
            ['sampling_request_id' => $b->id, 'dispatch_qty' => 2, 'batch_no' => 'BB'],
        ]);

        $rows = SamplingDispatch::whereIn('sampling_request_id', [$a->id, $b->id])->get();

        $this->assertCount(2, $rows);
        $this->assertSame(1, $rows->pluck('dispatch_ref_no')->unique()->count(), 'one dispatch number');
        $this->assertSame(1, $rows->pluck('invoice_no')->unique()->count(), 'one challan');
        $this->assertSame('closed', $a->fresh()->status);
        $this->assertSame('closed', $b->fresh()->status);
    }

    /** The dispatch picker returns not-ready lines too, so they can be greyed out. */
    public function testDispatchPickerIncludesNotReadyLines()
    {
        $lines = $this->raiseRequest();
        $this->approve($lines[0], 2);
        $this->markReady($lines[0]);
        $this->approve($lines[1], 2);   // left not ready

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/sample-dispatch/requests?user_id=' . $this->executive->id)
            ->assertStatus(200);

        $rows = collect($response->json('requests'))->keyBy('id');

        $this->assertTrue($rows[$lines[0]->id]['is_ready']);
        $this->assertFalse($rows[$lines[1]->id]['is_ready']);
    }

    public function testDispatchScreensRender()
    {
        $line = $this->raiseRequest()->first();
        $this->approve($line, 2);
        $this->markReady($line);
        $this->dispatch([['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_no' => 'B-9']]);

        $this->actingAs($this->admin)->get('/admin/sample-dispatch/create')->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch')
            ->assertStatus(200)
            ->assertSee($line->reference_no)
            ->assertSee('B-9');
    }

    /**
     * A voluntary dispatch has no request behind it, so one is created —
     * already approved, ready and flagged is_voluntarily — and closed at once.
     */
    public function testVoluntaryDispatchCreatesItsOwnRequest()
    {
        $before = SamplingRequest::count();

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'user',
                'dispatch_type'   => 'voluntary',
                'user_id'         => $this->executive->id,
                'voluntary_items' => [[
                    'product_id'           => $this->products[0]->id,
                    'approved_pack_size'   => 1,
                    'approved_no_of_packs' => 3,
                    'batch_no'             => 'B-VOL',
                    'dispatch_party_type'  => 'user',
                    'dispatch_user_id'     => $this->executive->id,
                    'remarks'              => 'Walk-in enquiry',
                ]],
            ])
            ->assertStatus(200);

        $this->assertSame($before + 1, SamplingRequest::count());

        $created = SamplingRequest::orderBy('id', 'DESC')->first();

        $this->assertTrue($created->is_voluntarily);
        $this->assertTrue($created->is_ready);
        $this->assertSame('closed', $created->status, 'the whole approved qty went out immediately');
        $this->assertEquals(3, $created->approved_qty, '1 kg x 3 packs');
        $this->assertEquals(3, $created->total_dispatched_qty);
        $this->assertEquals($this->executive->id, $created->user_id);
        $this->assertRegExp('/^SR-\d+\.1$/', $created->reference_no);
        $this->assertSame(1, SamplingDispatch::where('sampling_request_id', $created->id)->count());
    }

    /**
     * The form posts an empty string for the two party ids the chosen type
     * does not use; "" is not NULL to a foreign key, so it has to be blanked.
     */
    public function testBlankPartyIdsDoNotBreakTheInsert()
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'user',
                'dispatch_type'   => 'voluntary',
                'user_id'         => $this->executive->id,
                'voluntary_items' => [[
                    'product_id'           => $this->products[0]->id,
                    'approved_pack_size'   => 1,
                    'approved_no_of_packs' => 1,
                    'dispatch_party_type'  => 'user',
                    'dispatch_user_id'     => $this->executive->id,
                    // exactly what the browser sends for the unused pickers
                    'dispatch_dealer_id'   => '',
                    'dispatch_customer_id' => '',
                ]],
            ])
            ->assertStatus(200);

        $created = SamplingRequest::orderBy('id', 'DESC')->first();

        $this->assertNull($created->dispatch_dealer_id);
        $this->assertNull($created->dispatch_customer_id);
        $this->assertEquals($this->executive->id, $created->dispatch_user_id);
    }

    public function testVoluntaryDispatchNeedsAnEmployeeAndProducts()
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + ['answerable_type' => 'user', 'dispatch_type' => 'voluntary'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'voluntary_items']);
    }

    /**
     * Each request on a challan carries its own destination, so two requests
     * going to different parties is a normal save, not an error.
     */
    public function testRequestsOnOneChallanMayGoToDifferentParties()
    {
        $dealerId   = \App\Dealer::where('status', 1)->value('id');
        $customerId = \App\Customer::where('status', 1)->value('id');
        if (!$dealerId || !$customerId) {
            $this->markTestSkipped('Needs an active dealer and customer.');
        }

        $lines = $this->raiseRequest();
        $a = $lines[0];
        $b = $lines[1];

        foreach ([$a, $b] as $line) {
            $this->approve($line, 2);
            $this->markReady($line);
        }

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challan([
                ['sampling_request_id' => $a->id, 'dispatch_qty' => 2,
                 'dispatch_party_type' => 'dealer',   'dispatch_dealer_id'   => $dealerId],
                ['sampling_request_id' => $b->id, 'dispatch_qty' => 2,
                 'dispatch_party_type' => 'customer', 'dispatch_customer_id' => $customerId],
            ]))
            ->assertStatus(200);

        $rows = SamplingDispatch::whereIn('sampling_request_id', [$a->id, $b->id])->get();

        $this->assertSame(1, $rows->pluck('dispatch_ref_no')->unique()->count(), 'one challan');
        $this->assertEquals($dealerId, $rows->firstWhere('sampling_request_id', $a->id)->dispatch_dealer_id);
        $this->assertEquals($customerId, $rows->firstWhere('sampling_request_id', $b->id)->dispatch_customer_id);
    }

    /**
     * A voluntary dispatch picks one destination for all its products, and
     * each product still stores its own copy of it.
     */
    public function testVoluntaryProductsShareOneDestinationStoredPerRow()
    {
        $dealerId = \App\Dealer::where('status', 1)->value('id');
        if (!$dealerId) {
            $this->markTestSkipped('Needs an active dealer.');
        }

        $before = SamplingRequest::count();
        $party  = ['dispatch_party_type' => 'dealer', 'dispatch_dealer_id' => $dealerId,
                   'dispatch_address' => "Some Dealer\nSome Street"];

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'user',
                'dispatch_type'   => 'voluntary',
                'user_id'         => $this->executive->id,
                'voluntary_items' => [
                    $party + ['product_id' => $this->products[0]->id, 'approved_pack_size' => 1, 'approved_no_of_packs' => 2],
                    $party + ['product_id' => $this->products[1]->id, 'approved_pack_size' => 1, 'approved_no_of_packs' => 3],
                ],
            ])
            ->assertStatus(200);

        $this->assertSame($before + 2, SamplingRequest::count(), 'one request per product');

        $created = SamplingRequest::where('is_voluntarily', 1)->orderBy('id', 'DESC')->take(2)->get();

        foreach ($created as $row) {
            $this->assertEquals($dealerId, $row->dispatch_dealer_id, 'the one destination is copied onto each row');
            $this->assertSame('closed', $row->status);
        }
        $this->assertEqualsCanonicalizing([2.0, 3.0], $created->pluck('approved_qty')->map(function ($q) {
            return (float) $q;
        })->all());
    }

    /** The "All" screen lists every request whatever its status. */
    public function testAllScreenListsEveryStatus()
    {
        $lines = $this->raiseRequest();
        $this->approve($lines[1], 2);
        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $lines[2]->id . '/decline', ['decline_reason' => 'Not needed'])
            ->assertStatus(200);

        $response = $this->actingAs($this->admin)
            ->get('/admin/sampling-requests')
            ->assertStatus(200);

        foreach ($lines as $line) {
            $response->assertSee($line->reference_no);
        }

        // Its own status dropdown narrows it down.
        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests?status=declined')
            ->assertStatus(200)
            ->assertSee($lines[2]->reference_no)
            ->assertDontSee($lines[0]->reference_no);
    }

    /**
     * A voluntary sample was never requested by anyone, so it is kept out of
     * the request lists and lives on the dispatch screens instead.
     */
    public function testVoluntarySamplesAreHiddenFromTheRequestLists()
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'user',
                'dispatch_type'   => 'voluntary',
                'user_id'         => $this->executive->id,
                'voluntary_items' => [[
                    'product_id'           => $this->products[0]->id,
                    'approved_pack_size'   => 1,
                    'approved_no_of_packs' => 1,
                    'dispatch_party_type'  => 'user',
                    'dispatch_user_id'     => $this->executive->id,
                ]],
            ])
            ->assertStatus(200);

        $voluntary = SamplingRequest::where('is_voluntarily', 1)->orderBy('id', 'DESC')->firstOrFail();
        $this->assertSame('closed', $voluntary->status);

        foreach (['all', 'pending', 'approved', 'closed', 'declined'] as $status) {
            $this->actingAs($this->admin)
                ->get($status === 'all' ? '/admin/sampling-requests' : '/admin/sampling-requests/' . $status)
                ->assertStatus(200)
                ->assertDontSee($voluntary->reference_no);
        }

        // It is on the dispatch history, flagged as voluntary.
        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch')
            ->assertStatus(200)
            ->assertSee($voluntary->reference_no)
            ->assertSee('Voluntary');
    }

    /**
     * A dealer has nothing to dispatch against, so its samples are recorded as
     * voluntary and answerable to the dealer rather than to an executive.
     */
    public function testDealerDispatchIsAnswerableToTheDealer()
    {
        $dealer = \App\Dealer::where('status', 1)->whereNull('parent_id')->first();
        if (!$dealer) {
            $this->markTestSkipped('Needs an active head-office dealer.');
        }

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'dealer',
                'dealer_id'       => $dealer->id,
                // Deliberately omitted: a dealer dispatch is forced to voluntary.
                'voluntary_items' => [[
                    'product_id'           => $this->products[0]->id,
                    'approved_pack_size'   => 1,
                    'approved_no_of_packs' => 4,
                    'dispatch_party_type'  => 'dealer',
                    'dispatch_dealer_id'   => $dealer->id,
                ]],
            ])
            ->assertStatus(200);

        $created = SamplingRequest::orderBy('id', 'DESC')->first();

        $this->assertTrue($created->is_voluntarily);
        $this->assertSame('dealer', $created->raised_by);
        $this->assertEquals($dealer->id, $created->dealer_id);
        $this->assertNull($created->user_id, 'a dealer dispatch is not booked to an executive');
        $this->assertEquals($dealer->id, $created->dispatch_dealer_id);
        $this->assertEquals(4, $created->approved_qty);
        $this->assertSame('closed', $created->status);
    }

    public function testDealerDispatchNeedsADealer()
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'dealer',
                'voluntary_items' => [[
                    'product_id'           => $this->products[0]->id,
                    'approved_pack_size'   => 1,
                    'approved_no_of_packs' => 1,
                ]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dealer_id');
    }

    /** The listing can be narrowed to dealer dispatches and names the dealer. */
    public function testDispatchListingFiltersByDealer()
    {
        $dealer = \App\Dealer::where('status', 1)->whereNull('parent_id')->first();
        if (!$dealer) {
            $this->markTestSkipped('Needs an active head-office dealer.');
        }

        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challanHeader() + [
                'answerable_type' => 'dealer',
                'dealer_id'       => $dealer->id,
                'voluntary_items' => [[
                    'product_id'           => $this->products[0]->id,
                    'approved_pack_size'   => 1,
                    'approved_no_of_packs' => 1,
                    'dispatch_party_type'  => 'dealer',
                    'dispatch_dealer_id'   => $dealer->id,
                ]],
            ])
            ->assertStatus(200);

        $created = SamplingRequest::orderBy('id', 'DESC')->first();
        $name    = $dealer->business_name ?: $dealer->name;

        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch?answerable_type=dealer')
            ->assertStatus(200)
            ->assertSee($created->reference_no)
            ->assertSee(e($name));

        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch?answerable_type=user')
            ->assertStatus(200)
            ->assertDontSee($created->reference_no);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** @return \Illuminate\Support\Collection of SamplingRequest */
    private function raiseRequest()
    {
        return app(SamplingRequestService::class)->createFromApi([
            'sample_type' => 'free',
            'items' => [
                ['product_id' => $this->products[0]->id, 'pack_size' => 1, 'no_of_packs' => 1, 'qty' => 10, 'remarks' => 'Need only 250 gms'],
                ['product_id' => $this->products[1]->id, 'pack_size' => 1, 'no_of_packs' => 1, 'qty' => 5],
                ['product_id' => $this->products[2]->id, 'pack_size' => 1, 'no_of_packs' => 1, 'qty' => 2],
            ],
            'purpose_reason_for_request' => 'Lab testing',
            'dispatch_address'           => "Test Executive\nSome Street\nM. 9000000000",
        ], $this->executive->id);
    }

    /** Approve one product line for $packs packs of 1 kg. */
    private function approve(SamplingRequest $line, $packs, $packSize = 1)
    {
        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/approve', [
                'approved_pack_size'   => $packSize,
                'approved_no_of_packs' => $packs,
            ])
            ->assertStatus(200);
    }

    private function markReady(SamplingRequest $line)
    {
        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $line->id . '/mark-ready', ['ready_remarks' => 'Packed'])
            ->assertStatus(200);
    }

    private function dispatch(array $items)
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/sample-dispatch', $this->challan($items))
            ->assertStatus(200);
    }

    private function challan(array $items)
    {
        return $this->challanHeader() + [
            'answerable_type' => 'user',
            'dispatch_type'   => 'request',
            'items'           => $items,
        ];
    }

    private function challanHeader()
    {
        return [
            'dispatch_date' => date('Y-m-d'),
            'invoice_no'    => 'CH-' . uniqid(),
            'invoice_date'  => date('Y-m-d'),
            'transport'     => 'Courier',
            'lr_no'         => 'LR-' . uniqid(),
        ];
    }
}
