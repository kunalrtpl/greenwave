<?php

namespace Tests\Feature;

use App\AuthToken;
use App\FreeSamplingStock;
use App\Product;
use App\SamplingDispatch;
use App\SamplingRequest;
use App\SamplingRequestLog;
use App\Services\SamplingDispatchService;
use App\Services\SamplingRequestService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * What the app shows an executive about their own samples:
 *
 *   GET  /api/executive/sampling-requests            the requests and status
 *   GET  /api/executive/sample-dispatches            material on its way
 *   POST /api/executive/sample-dispatches/mark-received
 *
 * The rule that drives all of this: receipt is NOT a request status. A fully
 * dispatched request is `closed` whether or not the material has arrived, and
 * arrival lives on the dispatched line as is_received / received_at — the same
 * way is_ready is a flag inside `approved` rather than a status of its own.
 */
class SamplingReceiptApiTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $admin;

    /** @var \App\User */
    private $executive;

    /** @var \Illuminate\Support\Collection */
    private $products;

    /** @var string */
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin     = User::where('type', 'admin')->firstOrFail();
        $this->executive = User::marketingEmployees()->where('users.app_access', 'Yes')
            ->orderBy('users.id')->firstOrFail();
        $this->products  = Product::where('status', 1)->take(2)->get();

        if ($this->products->count() < 2) {
            $this->markTestSkipped('Needs at least two active products.');
        }

        $this->token = encrypt('user##-' . $this->executive->mobile);
        AuthToken::create(['type' => 'user', 'user_id' => $this->executive->id, 'auth_token' => $this->token]);

        Mail::fake();
    }

    private function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
    }

    /**
     * Raise $count products, approve and mark them ready, then dispatch the
     * whole approved qty — which closes every line.
     *
     * @return \Illuminate\Support\Collection of SamplingRequest
     */
    private function dispatchedRequests($count = 2, $qtyEach = 2)
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = ['product_id' => $this->products[$i]->id, 'pack_size' => 1,
                        'no_of_packs' => $qtyEach, 'qty' => $qtyEach];
        }

        $lines   = app(SamplingRequestService::class)->createFromApi(['items' => $items], $this->executive->id);
        $service = app(SamplingRequestService::class);

        $dispatchItems = [];
        foreach ($lines as $line) {
            $service->approve($line->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => $qtyEach]);
            $service->markReady($line->fresh(), []);

            $dispatchItems[] = ['sampling_request_id' => $line->id, 'dispatch_qty' => $qtyEach,
                                'batch_no' => 'B-' . $line->id,
                                'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id];
        }

        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user',
            'dispatch_type'   => 'request',
            'user_id'         => $this->executive->id,
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-R1',
            'transport'       => 'Courier',
            'lr_no'           => 'DKT-R1',
            'items'           => $dispatchItems,
        ]);

        return $lines->map(function ($l) { return $l->fresh(); });
    }

    /* ─────────────────────── The request listing ─────────────────────── */

    /** Each request carries its own status, labelled, with the qty breakdown. */
    public function testTheListingCarriesTheStatusAndQtyOfEachRequest()
    {
        $lines   = app(SamplingRequestService::class)->createFromApi([
            'items' => [
                ['product_id' => $this->products[0]->id, 'pack_size' => 1, 'no_of_packs' => 4, 'qty' => 4],
                ['product_id' => $this->products[1]->id, 'pack_size' => 1, 'no_of_packs' => 2, 'qty' => 2],
            ],
        ], $this->executive->id);

        // One approved for less than was asked for, one declined.
        $service = app(SamplingRequestService::class);
        $service->approve($lines[0]->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 3]);
        $service->decline($lines[1]->fresh(), ['decline_reason' => 'Sampled last month']);

        $rows = collect($this->getJson('/api/executive/sampling-requests', $this->headers())
            ->assertStatus(200)
            ->json('data.sampling_requests'))
            ->keyBy('reference_no');

        $approved = $rows[$lines[0]->reference_no];
        $this->assertSame('approved', $approved['status']);
        $this->assertSame('Approved (Pending Dispatch)', $approved['status_label']);
        $this->assertEquals(4, $approved['qty']['requested']);
        $this->assertEquals(3, $approved['qty']['approved'], 'the admin approved less than was asked for');
        $this->assertEquals(0, $approved['qty']['dispatched']);
        $this->assertEquals(3, $approved['qty']['pending']);
        $this->assertFalse($approved['is_ready'], 'approved is not the same as packed and ready');

        $declined = $rows[$lines[1]->reference_no];
        $this->assertSame('declined', $declined['status']);
        $this->assertSame('Sampled last month', $declined['decline_reason']);
    }

    /** The app badges its tabs from `counts`, which covers all four statuses. */
    public function testTheListingReturnsCountsPerStatus()
    {
        $this->dispatchedRequests(1);

        $counts = $this->getJson('/api/executive/sampling-requests', $this->headers())
            ->assertStatus(200)
            ->json('data.counts');

        foreach (['all', 'pending', 'approved', 'closed', 'declined'] as $key) {
            $this->assertArrayHasKey($key, $counts);
        }
        $this->assertGreaterThanOrEqual(1, $counts['closed']);
    }

    /** A request only ever lists for the executive who raised it. */
    public function testTheListingOnlyShowsTheCallersOwnRequests()
    {
        $other = User::marketingEmployees()->where('users.id', '!=', $this->executive->id)->firstOrFail();

        $theirs = app(SamplingRequestService::class)->createFromApi([
            'items' => [['product_id' => $this->products[0]->id, 'pack_size' => 1, 'no_of_packs' => 1, 'qty' => 1]],
        ], $other->id)->first();

        $refs = array_column($this->getJson('/api/executive/sampling-requests', $this->headers())
            ->assertStatus(200)->json('data.sampling_requests'), 'reference_no');

        $this->assertNotContains($theirs->reference_no, $refs);
    }

    /* ─────────────────────── The dispatch listing ─────────────────────── */

    /** The default list is what has not arrived yet. */
    public function testDispatchesDefaultToWhatHasNotBeenReceived()
    {
        $lines = $this->dispatchedRequests(2);

        $data = $this->getJson('/api/executive/sample-dispatches', $this->headers())
            ->assertStatus(200)
            ->json('data');

        $this->assertSame('pending', $data['status']);
        $this->assertGreaterThanOrEqual(2, count($data['dispatches']));

        foreach ($data['dispatches'] as $row) {
            $this->assertFalse($row['is_received']);
            $this->assertSame('In Transit', $row['status_label']);
        }

        // Each line names its challan and the request it came from.
        $mine = collect($data['dispatches'])->firstWhere('reference_no', $lines[0]->reference_no);
        $this->assertNotNull($mine);
        $this->assertSame('CH-R1', $mine['invoice_no']);
        $this->assertSame('DKT-R1', $mine['lr_no']);
        $this->assertSame('Courier', $mine['transport']);
        $this->assertEquals(2, $mine['dispatch_qty']);
    }

    /** ?group_by=challan nests the lines under the challan they went out on. */
    public function testDispatchesCanBeGroupedByChallan()
    {
        $this->dispatchedRequests(2);

        $challans = $this->getJson('/api/executive/sample-dispatches?group_by=challan', $this->headers())
            ->assertStatus(200)
            ->json('data.challans');

        $this->assertNotEmpty($challans);
        $challan = collect($challans)->firstWhere('invoice_no', 'CH-R1');

        $this->assertNotNull($challan);
        $this->assertCount(2, $challan['lines'], 'both products went out on the one challan');
        $this->assertEquals(4, $challan['total_qty']);
        $this->assertSame(2, $challan['pending_lines']);
    }

    /* ─────────────────────── Confirming receipt ─────────────────────── */

    /** Confirming moves the material from in transit into stock in hand. */
    public function testMarkingReceivedMovesTheStockAndStampsTheLine()
    {
        $lines    = $this->dispatchedRequests(1);
        $dispatch = SamplingDispatch::where('sampling_request_id', $lines[0]->id)->firstOrFail();

        $before = FreeSamplingStock::where('user_id', $this->executive->id)
            ->where('product_id', $dispatch->product_id)->firstOrFail();
        $inTransitBefore = $before->in_transit;
        $inHandBefore    = $before->stock_in_hand;

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$dispatch->id]], $this->headers())
            ->assertStatus(200)
            ->assertJson(['data' => ['received' => 1, 'already_received' => 0]]);

        $dispatch = $dispatch->fresh();
        $this->assertTrue((bool) $dispatch->is_received);
        $this->assertNotNull($dispatch->received_at);

        $after = $before->fresh();
        $this->assertEquals($inTransitBefore - 2, $after->in_transit, 'it is no longer in transit');
        $this->assertEquals($inHandBefore + 2, $after->stock_in_hand, 'it is stock in hand now');

        $this->assertSame(1, SamplingRequestLog::where('sampling_request_id', $lines[0]->id)
            ->where('action', SamplingRequestLog::ACTION_RECEIVED)->count(),
            'the receipt is on the request timeline');
    }

    /** The request's status is untouched: closed stays closed. */
    public function testReceiptIsNotARequestStatus()
    {
        $lines = $this->dispatchedRequests(1);
        $this->assertSame('closed', $lines[0]->status, 'fully dispatched, so closed');

        $dispatch = SamplingDispatch::where('sampling_request_id', $lines[0]->id)->firstOrFail();

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$dispatch->id]], $this->headers())->assertStatus(200);

        $this->assertSame('closed', $lines[0]->fresh()->status,
            'receipt is shown separately, it is not a status of its own');

        // …and the request listing reports it in the receipt block instead.
        $row = collect($this->getJson('/api/executive/sampling-requests', $this->headers())
            ->assertStatus(200)->json('data.sampling_requests'))
            ->firstWhere('reference_no', $lines[0]->reference_no);

        $this->assertSame('closed', $row['status']);
        $this->assertTrue($row['receipt']['all_received']);
        $this->assertSame(0, $row['receipt']['pending_lines']);
        $this->assertTrue($row['dispatches'][0]['is_received']);
    }

    /** A line already confirmed is reported back, and its stock is left alone. */
    public function testConfirmingTwiceDoesNotMoveTheStockTwice()
    {
        $lines    = $this->dispatchedRequests(1);
        $dispatch = SamplingDispatch::where('sampling_request_id', $lines[0]->id)->firstOrFail();

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$dispatch->id]], $this->headers())->assertStatus(200);

        $stock = FreeSamplingStock::where('user_id', $this->executive->id)
            ->where('product_id', $dispatch->product_id)->firstOrFail();
        $inHand    = $stock->stock_in_hand;
        $inTransit = $stock->in_transit;

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$dispatch->id]], $this->headers())
            ->assertStatus(200)
            ->assertJson(['data' => ['received' => 0, 'already_received' => 1]]);

        $stock = $stock->fresh();
        $this->assertEquals($inHand, $stock->stock_in_hand, 'the qty is not credited again');
        $this->assertEquals($inTransit, $stock->in_transit);
        $this->assertSame(1, SamplingRequestLog::where('sampling_request_id', $lines[0]->id)
            ->where('action', SamplingRequestLog::ACTION_RECEIVED)->count(),
            'and it is not logged again');
    }

    /** Received lines leave the pending list and show up in the history. */
    public function testReceivedLinesMoveBetweenTheTwoLists()
    {
        $lines    = $this->dispatchedRequests(2);
        $first    = SamplingDispatch::where('sampling_request_id', $lines[0]->id)->firstOrFail();

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$first->id]], $this->headers())->assertStatus(200);

        $pending = $this->getJson('/api/executive/sample-dispatches?status=pending', $this->headers())
            ->assertStatus(200)->json('data.dispatches');
        $this->assertNotContains($first->id, array_column($pending, 'id'));

        $received = $this->getJson('/api/executive/sample-dispatches?status=received', $this->headers())
            ->assertStatus(200)->json('data.dispatches');
        $this->assertContains($first->id, array_column($received, 'id'));

        $all = $this->getJson('/api/executive/sample-dispatches?status=all', $this->headers())
            ->assertStatus(200)->json('data');
        $this->assertContains($first->id, array_column($all['dispatches'], 'id'));
        $this->assertGreaterThanOrEqual(1, $all['counts']['received']);
        $this->assertGreaterThanOrEqual(1, $all['counts']['pending']);
    }

    /** Someone else's dispatch can neither be listed nor confirmed. */
    public function testAnEmployeeCannotConfirmSomeoneElsesDispatch()
    {
        $other = User::marketingEmployees()->where('users.id', '!=', $this->executive->id)
            ->whereNotNull('users.mobile')->firstOrFail();

        $line = app(SamplingRequestService::class)->createFromApi([
            'items' => [['product_id' => $this->products[0]->id, 'pack_size' => 1, 'no_of_packs' => 1, 'qty' => 1]],
        ], $other->id)->first();

        $service = app(SamplingRequestService::class);
        $service->approve($line->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 1]);
        $service->markReady($line->fresh(), []);

        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user', 'dispatch_type' => 'request', 'user_id' => $other->id,
            'dispatch_date' => date('Y-m-d'), 'invoice_no' => 'CH-OTHER',
            'transport' => 'Courier', 'lr_no' => 'DKT-OTHER',
            'items' => [['sampling_request_id' => $line->id, 'dispatch_qty' => 1,
                         'dispatch_party_type' => 'user', 'dispatch_user_id' => $other->id]],
        ]);

        $theirs = SamplingDispatch::where('sampling_request_id', $line->id)->firstOrFail();

        $listed = $this->getJson('/api/executive/sample-dispatches?status=all', $this->headers())
            ->assertStatus(200)->json('data.dispatches');
        $this->assertNotContains($theirs->id, array_column($listed, 'id'));

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$theirs->id]], $this->headers())
            ->assertStatus(422);

        $this->assertFalse((bool) $theirs->fresh()->is_received);
    }

    /** Several lines of one challan can be confirmed in one post. */
    public function testSeveralLinesCanBeConfirmedTogether()
    {
        $lines = $this->dispatchedRequests(2);
        $ids   = SamplingDispatch::whereIn('sampling_request_id', $lines->pluck('id'))->pluck('id')->all();

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => $ids], $this->headers())
            ->assertStatus(200)
            ->assertJson(['data' => ['received' => 2]]);

        $this->assertSame(0, SamplingDispatch::whereIn('id', $ids)->notReceived()->count());
    }

    public function testAnEmptyOrUnknownDispatchIdIsRejected()
    {
        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => []], $this->headers())
            ->assertStatus(422);

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [99999999]], $this->headers())
            ->assertStatus(422);
    }

    /**
     * Without a token nothing is served. The app's own middleware answers the
     * missing header before the controller is reached, so what matters is that
     * the body never reports success.
     */
    public function testBothEndpointsNeedAValidToken()
    {
        $this->getJson('/api/executive/sample-dispatches', ['Accept' => 'application/json'])
            ->assertJson(['status' => false]);

        $this->postJson('/api/executive/sample-dispatches/mark-received', ['dispatch_ids' => [1]],
            ['Accept' => 'application/json'])
            ->assertJson(['status' => false]);
    }

    /* ─────────────────────── The admin's side ─────────────────────── */

    /** The admin screens show receipt beside the dispatch, and filter on it. */
    public function testTheAdminDispatchHistoryShowsAndFiltersReceipt()
    {
        $lines    = $this->dispatchedRequests(1);
        $dispatch = SamplingDispatch::where('sampling_request_id', $lines[0]->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch?receipt=not_received')
            ->assertStatus(200)
            ->assertSee($dispatch->dispatch_ref_no)
            ->assertSee('Not received');

        $this->postJson('/api/executive/sample-dispatches/mark-received',
            ['dispatch_ids' => [$dispatch->id]], $this->headers())->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch?receipt=received')
            ->assertStatus(200)
            ->assertSee($dispatch->dispatch_ref_no)
            ->assertSee('Received');

        $this->actingAs($this->admin)
            ->get('/admin/sample-dispatch?receipt=not_received')
            ->assertStatus(200)
            ->assertDontSee($dispatch->dispatch_ref_no);

        // And the request's own page says so against the dispatched line.
        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $lines[0]->id)
            ->assertStatus(200)
            ->assertSee('Received');
    }
}
