<?php

namespace Tests\Feature;

use App\AuthToken;
use App\Mail\DynamicMail;
use App\Product;
use App\SamplingRequest;
use App\SamplingRequestQuery;
use App\Services\SamplingQueryService;
use App\Services\SamplingRequestService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The query thread on a sample request.
 *
 * The rules that matter: posting is open while the request is pending or
 * approved and shut once it is closed or declined, each side's unread count
 * only counts the other side's messages, and every message emails the other
 * side.
 */
class SamplingQueryTest extends TestCase
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
        $this->executive = User::marketingEmployees()->whereNotNull('users.email')
            ->where('users.app_access', 'Yes')->orderBy('users.id')->firstOrFail();
        $this->products  = Product::where('status', 1)->take(2)->get();

        if ($this->products->count() < 2) {
            $this->markTestSkipped('Needs at least two active products.');
        }

        $this->token = encrypt('user##-' . $this->executive->mobile);
        AuthToken::create(['type' => 'user', 'user_id' => $this->executive->id, 'auth_token' => $this->token]);

        Mail::fake();
    }

    private function apiHeaders()
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
    }

    /** @return \App\SamplingRequest */
    private function aRequest()
    {
        return app(SamplingRequestService::class)->createFromApi([
            'items' => [['product_id' => $this->products[0]->id, 'pack_size' => 1, 'no_of_packs' => 2, 'qty' => 2]],
        ], $this->executive->id)->first();
    }

    private function sentFor($eventKey)
    {
        $count = 0;
        Mail::assertSent(DynamicMail::class, function ($mail) use ($eventKey, &$count) {
            if ($mail->template->event_key === $eventKey) {
                $count++;
            }
            return true;
        });
        return $count;
    }

    /* ─────────────────────────── Posting ─────────────────────────── */

    public function testEmployeeCanPostAQueryFromTheApp()
    {
        $request = $this->aRequest();

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query', [
            'message' => 'Can I get 5 kg instead of 2?',
        ], $this->apiHeaders())->assertStatus(200);

        $query = SamplingRequestQuery::where('sampling_request_id', $request->id)->firstOrFail();

        $this->assertSame('employee', $query->sender_type);
        $this->assertEquals($this->executive->id, $query->sender_id);
        $this->assertFalse($query->admin_read, 'the admin has not seen it yet');
        $this->assertTrue($query->user_read, 'the writer has obviously seen it');
    }

    public function testAdminCanReplyFromThePanel()
    {
        $request = $this->aRequest();

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $request->id . '/raise-query', [
                'message' => 'Only 2 kg can be spared this month.',
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $query = SamplingRequestQuery::where('sampling_request_id', $request->id)->firstOrFail();

        $this->assertSame('admin', $query->sender_type);
        $this->assertTrue($query->admin_read);
        $this->assertFalse($query->user_read, 'the employee has not seen it yet');
    }

    /* ─────────────────────────── Notifications ─────────────────────────── */

    public function testEachMessageNotifiesTheOtherSide()
    {
        $request = $this->aRequest();

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query', [
            'message' => 'A question',
        ], $this->apiHeaders())->assertStatus(200);

        $this->assertSame(1, $this->sentFor('sample_query_from_employee_admin'));
        $this->assertSame(0, $this->sentFor('sample_query_from_admin_employee'));

        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $request->id . '/raise-query', ['message' => 'An answer'])
            ->assertStatus(200);

        $this->assertSame(1, $this->sentFor('sample_query_from_admin_employee'));
    }

    /* ─────────────────────────── Unread counts ─────────────────────────── */

    public function testUnreadCountsOnlyCountTheOtherSide()
    {
        $request = $this->aRequest();
        $service = app(SamplingQueryService::class);

        // Two from the employee, one from the admin.
        foreach (['one', 'two'] as $m) {
            $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
                ['message' => $m], $this->apiHeaders())->assertStatus(200);
        }
        $this->actingAs($this->admin)
            ->post('/admin/sampling-requests/' . $request->id . '/raise-query', ['message' => 'three'])
            ->assertStatus(200);

        $this->assertSame(2, $service->unreadForAdmin($request->id), 'the two employee messages');
        $this->assertSame(1, $service->unreadForEmployee($request->id), 'the one admin message');

        // Opening the thread as the admin clears only the admin's side.
        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $request->id . '/queries')
            ->assertStatus(200);

        $this->assertSame(0, $service->unreadForAdmin($request->id));
        $this->assertSame(1, $service->unreadForEmployee($request->id), 'the app still has one to read');

        // And the app opening it clears its own.
        $this->getJson('/api/executive/sampling-requests/' . $request->id . '/queries', $this->apiHeaders())
            ->assertStatus(200);

        $this->assertSame(0, $service->unreadForEmployee($request->id));
    }

    /* ─────────────────────────── Closed threads ─────────────────────────── */

    public function testPostingIsRefusedOnceTheRequestIsDeclined()
    {
        $request = $this->aRequest();

        app(SamplingRequestService::class)->decline($request->fresh(), ['decline_reason' => 'Not needed']);

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
            ['message' => 'Why?'], $this->apiHeaders())->assertStatus(422);

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $request->id . '/raise-query', ['message' => 'Because'])
            ->assertStatus(422);

        $this->assertSame(0, SamplingRequestQuery::where('sampling_request_id', $request->id)->count());
    }

    public function testPostingIsRefusedOnceTheRequestIsClosed()
    {
        $request = $this->aRequest();
        $service = app(SamplingRequestService::class);

        $service->approve($request->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 2]);
        $service->markReady($request->fresh(), []);

        app(\App\Services\SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user', 'dispatch_type' => 'request', 'user_id' => $this->executive->id,
            'dispatch_date' => date('Y-m-d'), 'invoice_no' => 'CH-Q1',
            'transport' => 'Courier', 'lr_no' => 'DKT-Q1',
            'items' => [['sampling_request_id' => $request->id, 'dispatch_qty' => 2,
                         'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id]],
        ]);

        $this->assertSame('closed', $request->fresh()->status);

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
            ['message' => 'Anything else?'], $this->apiHeaders())->assertStatus(422);
    }

    /** A shut thread is still readable — only posting stops. */
    public function testHistoryStaysReadableAfterTheRequestIsDeclined()
    {
        $request = $this->aRequest();

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
            ['message' => 'Please approve soon'], $this->apiHeaders())->assertStatus(200);

        app(SamplingRequestService::class)->decline($request->fresh(), ['decline_reason' => 'Out of stock']);

        $thread = $this->getJson('/api/executive/sampling-requests/' . $request->id . '/queries', $this->apiHeaders())
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(1, $thread['queries'], 'the message is still there');
        $this->assertFalse($thread['can_post']);
        $this->assertNotEmpty($thread['closed_reason']);

        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $request->id . '/queries')
            ->assertStatus(200)
            ->assertJson(['can_post' => false]);
    }

    /* ─────────────────────────── Ownership ─────────────────────────── */

    public function testAnEmployeeCannotPostOnSomeoneElsesRequest()
    {
        $other = User::marketingEmployees()->where('users.id', '!=', $this->executive->id)->firstOrFail();

        $theirRequest = app(SamplingRequestService::class)->createFromApi([
            'items' => [['product_id' => $this->products[0]->id, 'pack_size' => 1, 'no_of_packs' => 1, 'qty' => 1]],
        ], $other->id)->first();

        $this->postJson('/api/executive/sampling-requests/' . $theirRequest->id . '/query',
            ['message' => 'Not mine'], $this->apiHeaders())->assertStatus(404);

        $this->assertSame(0, SamplingRequestQuery::where('sampling_request_id', $theirRequest->id)->count());
    }

    public function testAnEmptyMessageIsRejected()
    {
        $request = $this->aRequest();

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
            ['message' => ''], $this->apiHeaders())->assertStatus(422);

        $this->actingAs($this->admin)
            ->postJson('/admin/sampling-requests/' . $request->id . '/raise-query', ['message' => '   '])
            ->assertStatus(422);
    }

    /* ─────────────────────────── Screens ─────────────────────────── */

    public function testTheListingShowsTheQueryButtonAndUnreadCount()
    {
        $request = $this->aRequest();

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
            ['message' => 'Unread by the admin'], $this->apiHeaders())->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/pending')
            ->assertStatus(200)
            ->assertSee('sq-open')
            ->assertSee('unread');
    }

    public function testTheDetailScreenShowsTheThread()
    {
        $request = $this->aRequest();

        $this->postJson('/api/executive/sampling-requests/' . $request->id . '/query',
            ['message' => 'A question from the field'], $this->apiHeaders())->assertStatus(200);

        $this->actingAs($this->admin)
            ->get('/admin/sampling-requests/' . $request->id)
            ->assertStatus(200)
            ->assertSee('A question from the field')
            ->assertSee('Write a Reply');
    }
}
