<?php

namespace Tests\Feature;

use App\AuthToken;
use App\FreeSamplingStock;
use App\Mail\DynamicMail;
use App\Product;
use App\StockTransfer;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Stock Transfer — one marketing employee hands free-sample stock to another.
 *
 * The rule the whole module turns on: the qty leaves the sender's usable stock
 * the moment the request goes out, so the same material cannot be offered to
 * two people. It comes back only if the transfer is refused or withdrawn.
 */
class StockTransferApiTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $sender;

    /** @var \App\User */
    private $receiver;

    /** @var \Illuminate\Support\Collection */
    private $products;

    /** @var string */
    private $senderToken;

    /** @var string */
    private $receiverToken;

    protected function setUp(): void
    {
        parent::setUp();

        $employees = User::marketingEmployees()->orderBy('users.id')->take(2)->get();

        if ($employees->count() < 2) {
            $this->markTestSkipped('Needs two marketing employees.');
        }

        $this->sender   = $employees[0];
        $this->receiver = $employees[1];

        $this->products = Product::where('status', 1)->take(2)->get();
        if ($this->products->count() < 2) {
            $this->markTestSkipped('Needs two active products.');
        }

        $this->senderToken   = $this->tokenFor($this->sender);
        $this->receiverToken = $this->tokenFor($this->receiver);

        // The sender holds 10 Kg of the first product and 4 Kg of the second.
        $this->giveStock($this->sender, $this->products[0], 10);
        $this->giveStock($this->sender, $this->products[1], 4);

        Mail::fake();
    }

    private function tokenFor(User $user)
    {
        $token = encrypt('user##-' . $user->mobile . '-' . $user->id);
        AuthToken::create(['type' => 'user', 'user_id' => $user->id, 'auth_token' => $token]);

        return $token;
    }

    private function headers($token)
    {
        return ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'];
    }

    private function asSender()
    {
        return $this->headers($this->senderToken);
    }

    private function asReceiver()
    {
        return $this->headers($this->receiverToken);
    }

    private function giveStock(User $user, Product $product, $qty)
    {
        // The model guards every column, so the keys are set one by one.
        $stock = $this->stockOf($user, $product) ?: new FreeSamplingStock();

        $stock->user_id                 = $user->id;
        $stock->product_id              = $product->id;
        $stock->stock_in_hand           = $qty;
        $stock->blocked_stock           = 0;
        $stock->in_transit              = 0;
        $stock->pending_orders          = 0;
        $stock->pending_customer_orders = 0;
        $stock->save();

        return $stock;
    }

    /** @return \App\FreeSamplingStock|null */
    private function stockOf(User $user, Product $product)
    {
        return FreeSamplingStock::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
    }

    /** Raise a transfer of $qty of the first product. @return int the id */
    private function raise($qty = 3, array $override = [])
    {
        $payload = $override + [
            'to_user_id' => $this->receiver->id,
            'remarks'    => 'Spare stock for your trial',
            'items'      => [['product_id' => $this->products[0]->id, 'qty' => $qty]],
        ];

        return $this->postJson('/api/executive/stock-transfers', $payload, $this->asSender())
            ->assertStatus(200)
            ->json('data.stock_transfer.id');
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

    /* ═══════════════════════ Raising ═══════════════════════ */

    public function testRaisingATransferBlocksTheStockImmediately()
    {
        $id = $this->raise(3);

        $transfer = StockTransfer::findOrFail($id);
        $this->assertSame('pending', $transfer->status);
        $this->assertSame('ST-' . $transfer->transfer_no, $transfer->reference_no);
        $this->assertEquals($this->receiver->id, $transfer->to_user_id);
        $this->assertEquals($this->sender->id, $transfer->from_user_id);

        $stock = $this->stockOf($this->sender, $this->products[0]);
        $this->assertEquals(7, $stock->stock_in_hand, '3 Kg has left the usable stock');
        $this->assertEquals(3, $stock->blocked_stock, 'and is blocked behind the request');

        // Nothing reaches the receiver until they accept.
        $this->assertNull($this->stockOf($this->receiver, $this->products[0]));
    }

    /** The point of blocking: the same stock cannot be offered twice. */
    public function testTheSameStockCannotBeOfferedTwice()
    {
        $this->raise(7);

        // 3 Kg left, so a second 7 Kg offer must fail.
        $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $this->receiver->id,
            'items'      => [['product_id' => $this->products[0]->id, 'qty' => 7]],
        ], $this->asSender())->assertStatus(422);

        $stock = $this->stockOf($this->sender, $this->products[0]);
        $this->assertEquals(3, $stock->stock_in_hand, 'the first block still stands');
        $this->assertEquals(7, $stock->blocked_stock);
        $this->assertSame(1, StockTransfer::where('from_user_id', $this->sender->id)->count(),
            'the refused request was not created');
    }

    public function testAnEmployeeCannotTransferStockTheyDoNotHave()
    {
        $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $this->receiver->id,
            'items'      => [['product_id' => $this->products[0]->id, 'qty' => 99]],
        ], $this->asSender())->assertStatus(422);

        $this->assertEquals(10, $this->stockOf($this->sender, $this->products[0])->stock_in_hand);
    }

    /** Several products on one transfer, all blocked together. */
    public function testATransferCanCarrySeveralProducts()
    {
        $id = $this->raise(2, ['items' => [
            ['product_id' => $this->products[0]->id, 'qty' => 2],
            ['product_id' => $this->products[1]->id, 'qty' => 4],
        ]]);

        $this->assertCount(2, StockTransfer::findOrFail($id)->items);
        $this->assertEquals(2, $this->stockOf($this->sender, $this->products[0])->blocked_stock);
        $this->assertEquals(4, $this->stockOf($this->sender, $this->products[1])->blocked_stock);
    }

    /** One product short in a multi-product transfer and nothing is saved. */
    public function testOneShortProductRollsTheWholeTransferBack()
    {
        $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $this->receiver->id,
            'items'      => [
                ['product_id' => $this->products[0]->id, 'qty' => 2],
                ['product_id' => $this->products[1]->id, 'qty' => 99],
            ],
        ], $this->asSender())->assertStatus(422);

        $this->assertSame(0, StockTransfer::where('from_user_id', $this->sender->id)->count());
        $this->assertEquals(10, $this->stockOf($this->sender, $this->products[0])->stock_in_hand,
            'the first product was not left blocked');
        $this->assertEquals(0, $this->stockOf($this->sender, $this->products[0])->blocked_stock);
    }

    /* ═══════════════════════ Who may use it ═══════════════════════ */

    public function testStockCannotBeTransferredToYourself()
    {
        $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $this->sender->id,
            'items'      => [['product_id' => $this->products[0]->id, 'qty' => 1]],
        ], $this->asSender())->assertStatus(422);
    }

    /** Only marketing employees, either end. */
    public function testStockCannotBeTransferredToANonMarketingUser()
    {
        $marketingIds = User::marketingEmployees()->pluck('users.id')->all();

        $outsider = User::where('status', 1)->whereNotIn('id', $marketingIds)->first();
        if (!$outsider) {
            $this->markTestSkipped('Needs a user outside the marketing department.');
        }

        $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $outsider->id,
            'items'      => [['product_id' => $this->products[0]->id, 'qty' => 1]],
        ], $this->asSender())->assertStatus(422);

        $this->assertEquals(10, $this->stockOf($this->sender, $this->products[0])->stock_in_hand);
    }

    /* ═══════════════════════ Approving ═══════════════════════ */

    public function testApprovingMovesTheStockToTheReceiver()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(200)
            ->assertJson(['data' => ['stock_transfer' => ['status' => 'approved']]]);

        $senderStock = $this->stockOf($this->sender, $this->products[0]);
        $this->assertEquals(7, $senderStock->stock_in_hand, 'the sender does not get it back');
        $this->assertEquals(0, $senderStock->blocked_stock, 'and nothing stays blocked');

        $receiverStock = $this->stockOf($this->receiver, $this->products[0]);
        $this->assertNotNull($receiverStock, 'the receiver gets a stock row even with none before');
        $this->assertEquals(3, $receiverStock->stock_in_hand);

        $transfer = StockTransfer::findOrFail($id);
        $this->assertSame('approved', $transfer->status);
        $this->assertNotNull($transfer->approved_at);
    }

    /** An employee who already holds the product has it added to. */
    public function testApprovingAddsToStockTheReceiverAlreadyHolds()
    {
        $this->giveStock($this->receiver, $this->products[0], 5);

        $id = $this->raise(3);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(200);

        $this->assertEquals(8, $this->stockOf($this->receiver, $this->products[0])->stock_in_hand);
    }

    public function testOnlyTheReceivingEmployeeCanApprove()
    {
        $id = $this->raise(3);

        // The sender cannot approve their own transfer.
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asSender())
            ->assertStatus(422);

        $this->assertSame('pending', StockTransfer::findOrFail($id)->status);
    }

    public function testATransferCannotBeApprovedTwice()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(200);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(422);

        $this->assertEquals(3, $this->stockOf($this->receiver, $this->products[0])->stock_in_hand,
            'the qty is credited once, not twice');
    }

    /* ═══════════════════════ Declining ═══════════════════════ */

    public function testDecliningGivesTheStockBack()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/decline',
            ['decline_reason' => 'I have enough of this one'], $this->asReceiver())
            ->assertStatus(200)
            ->assertJson(['data' => ['stock_transfer' => ['status' => 'declined']]]);

        $stock = $this->stockOf($this->sender, $this->products[0]);
        $this->assertEquals(10, $stock->stock_in_hand, 'all 10 Kg are usable again');
        $this->assertEquals(0, $stock->blocked_stock);

        $this->assertNull($this->stockOf($this->receiver, $this->products[0]));
        $this->assertSame('I have enough of this one', StockTransfer::findOrFail($id)->decline_reason);
    }

    public function testDecliningNeedsAReason()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/decline', [], $this->asReceiver())
            ->assertStatus(422);

        $this->assertSame('pending', StockTransfer::findOrFail($id)->status);
    }

    /* ═══════════════════════ Cancelling ═══════════════════════ */

    public function testTheSenderCanCancelWhileItIsPending()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/cancel', [], $this->asSender())
            ->assertStatus(200)
            ->assertJson(['data' => ['stock_transfer' => ['status' => 'cancelled']]]);

        $stock = $this->stockOf($this->sender, $this->products[0]);
        $this->assertEquals(10, $stock->stock_in_hand, 'the blocked qty is released');
        $this->assertEquals(0, $stock->blocked_stock);
    }

    public function testTheSenderCannotCancelOnceItIsApproved()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(200);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/cancel', [], $this->asSender())
            ->assertStatus(422);

        $this->assertSame('approved', StockTransfer::findOrFail($id)->status);
        $this->assertEquals(3, $this->stockOf($this->receiver, $this->products[0])->stock_in_hand,
            'the receiver keeps what they accepted');
    }

    public function testTheReceiverCannotCancelSomeoneElsesTransfer()
    {
        $id = $this->raise(3);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/cancel', [], $this->asReceiver())
            ->assertStatus(422);

        $this->assertSame('pending', StockTransfer::findOrFail($id)->status);
    }

    /* ═══════════════════════ Listings ═══════════════════════ */

    /** The dedicated list the receiving employee acts on. */
    public function testThePendingApprovalsListIsTheReceiversToDoList()
    {
        $id = $this->raise(3);

        $data = $this->getJson('/api/executive/stock-transfers/pending-approvals', $this->asReceiver())
            ->assertStatus(200)
            ->json('data');

        $this->assertSame(1, $data['pending_count']);
        $this->assertSame($id, $data['stock_transfers'][0]['id']);
        $this->assertTrue($data['stock_transfers'][0]['can_approve']);
        $this->assertFalse($data['stock_transfers'][0]['can_cancel'], 'the receiver cannot cancel it');
        $this->assertSame('received', $data['stock_transfers'][0]['direction']);

        // The sender has nothing to approve — it is not their move.
        $this->assertSame(0, $this->getJson('/api/executive/stock-transfers/pending-approvals', $this->asSender())
            ->assertStatus(200)->json('data.pending_count'));
    }

    /** Once settled it drops off the to-do list. */
    public function testAnApprovedTransferLeavesThePendingApprovalsList()
    {
        $id = $this->raise(3);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver());

        $this->assertSame(0, $this->getJson('/api/executive/stock-transfers/pending-approvals', $this->asReceiver())
            ->assertStatus(200)->json('data.pending_count'));
    }

    public function testTheListingSeparatesSentFromReceived()
    {
        $id = $this->raise(3);

        $sent = $this->getJson('/api/executive/stock-transfers?direction=sent', $this->asSender())
            ->assertStatus(200)->json('data.stock_transfers');
        $this->assertSame('sent', $sent[0]['direction']);
        $this->assertTrue($sent[0]['can_cancel'], 'the sender may withdraw it');
        $this->assertFalse($sent[0]['can_approve']);

        $received = $this->getJson('/api/executive/stock-transfers?direction=received', $this->asReceiver())
            ->assertStatus(200)->json('data.stock_transfers');
        $this->assertSame($id, $received[0]['id']);

        // A sent transfer is not in the sender's received list.
        $this->assertCount(0, $this->getJson('/api/executive/stock-transfers?direction=received', $this->asSender())
            ->assertStatus(200)->json('data.stock_transfers'));
    }

    public function testTheListingCountsIncludeWhatIsWaitingOnMe()
    {
        $this->raise(3);

        $counts = $this->getJson('/api/executive/stock-transfers', $this->asReceiver())
            ->assertStatus(200)->json('data.counts');

        $this->assertSame(1, $counts['pending']);
        $this->assertSame(1, $counts['awaiting_my_approval']);

        $senderCounts = $this->getJson('/api/executive/stock-transfers', $this->asSender())
            ->assertStatus(200)->json('data.counts');
        $this->assertSame(0, $senderCounts['awaiting_my_approval'], 'the sender is not waiting on themselves');
    }

    /** A transfer belongs to the two employees on it and nobody else. */
    public function testAThirdEmployeeCannotSeeOrActOnIt()
    {
        $third = User::marketingEmployees()
            ->whereNotIn('users.id', [$this->sender->id, $this->receiver->id])
            ->first();

        if (!$third) {
            $this->markTestSkipped('Needs a third marketing employee.');
        }

        $id      = $this->raise(3);
        $headers = $this->headers($this->tokenFor($third));

        $this->getJson('/api/executive/stock-transfers/' . $id, $headers)->assertStatus(404);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $headers)->assertStatus(404);

        $this->assertSame('pending', StockTransfer::findOrFail($id)->status);
    }

    /* ═══════════════════════ Stock endpoint ═══════════════════════ */

    public function testTheStockEndpointShowsWhatIsFreeAndWhatIsBlocked()
    {
        $this->raise(3);

        $stock = collect($this->getJson('/api/executive/stock-transfers/stock', $this->asSender())
            ->assertStatus(200)->json('data.stock'))
            ->firstWhere('product_id', $this->products[0]->id);

        $this->assertEquals(7, $stock['available_qty'], 'what is left to offer');
        $this->assertEquals(3, $stock['blocked_qty'], 'what is already offered');
    }

    public function testTheEmployeeListExcludesTheCallerAndIsMarketingOnly()
    {
        $ids = array_column($this->getJson('/api/executive/stock-transfers/employees', $this->asSender())
            ->assertStatus(200)->json('data.employees'), 'id');

        $this->assertNotContains($this->sender->id, $ids, 'you cannot transfer to yourself');
        $this->assertContains($this->receiver->id, $ids);

        $marketingIds = User::marketingEmployees()->pluck('users.id')->all();
        foreach ($ids as $id) {
            $this->assertContains($id, $marketingIds, 'marketing employees only');
        }
    }

    /* ═══════════════════════ Emails ═══════════════════════ */

    /** Raising it tells the employee who has to answer. */
    public function testRaisingEmailsTheReceivingEmployee()
    {
        config(['mail.redirect_to' => null]);

        $this->raise(3);

        $this->assertSame(1, $this->sentFor('stock_transfer_requested_employee'));

        $receiver = $this->receiver;
        Mail::assertSent(DynamicMail::class, function ($mail) use ($receiver) {
            return $mail->template->event_key === 'stock_transfer_requested_employee'
                && $mail->hasTo($receiver->email);
        });
    }

    /** Accepting tells the employee who offered it. */
    public function testApprovingEmailsTheSendingEmployee()
    {
        config(['mail.redirect_to' => null]);

        $id = $this->raise(3);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(200);

        $this->assertSame(1, $this->sentFor('stock_transfer_decided_employee'));

        $sender = $this->sender;
        Mail::assertSent(DynamicMail::class, function ($mail) use ($sender) {
            return $mail->template->event_key === 'stock_transfer_decided_employee'
                && $mail->hasTo($sender->email);
        });
    }

    /** So does refusing — otherwise the sender would never find out. */
    public function testDecliningAlsoEmailsTheSendingEmployee()
    {
        $id = $this->raise(3);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/decline',
            ['decline_reason' => 'Not needed'], $this->asReceiver())->assertStatus(200);

        $this->assertSame(1, $this->sentFor('stock_transfer_decided_employee'));
    }

    /** Cancelling is the sender's own doing, so nobody is emailed. */
    public function testCancellingSendsNoEmail()
    {
        $id = $this->raise(3);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/cancel', [], $this->asSender())
            ->assertStatus(200);

        $this->assertSame(0, $this->sentFor('stock_transfer_decided_employee'));
    }

    /** With MAIL_REDIRECT_TO set, nothing reaches the real employees. */
    public function testRedirectKeepsTestMailOffRealEmployees()
    {
        config(['mail.redirect_to' => 'tester@example.com']);

        $this->raise(3);

        Mail::assertSent(DynamicMail::class, function ($mail) {
            return $mail->hasTo('tester@example.com');
        });

        $receiver = $this->receiver;
        Mail::assertNotSent(DynamicMail::class, function ($mail) use ($receiver) {
            return $mail->hasTo($receiver->email);
        });
    }

    /** Both emails render — placeholders in the subject included. */
    public function testBothEmailsRender()
    {
        $id = $this->raise(3);
        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->asReceiver())
            ->assertStatus(200);

        $seen = [];

        Mail::assertSent(DynamicMail::class, function ($mail) use (&$seen) {
            $html    = view($mail->template->blade_view, $mail->mailData)->render();
            $subject = $mail->resolvedSubject();

            $seen[$mail->template->event_key] = ['html' => $html, 'subject' => $subject];

            return true;
        });

        $requested = $seen['stock_transfer_requested_employee'];
        $this->assertContains($this->products[0]->product_name, $requested['html']);
        $this->assertContains('ST-', $requested['subject']);
        $this->assertNotContains('{transfer.', $requested['subject'], 'the subject placeholder resolved');

        $decided = $seen['stock_transfer_decided_employee'];
        $this->assertContains('accepted', strtolower($decided['html']));
        $this->assertContains('Approved', $decided['subject'], 'the status is in the subject');
        $this->assertNotContains('{transfer.', $decided['subject']);
    }

    /** A failing mailer must never undo the transfer itself. */
    public function testAnEmailFailureDoesNotBreakTheTransfer()
    {
        \App\EmailTemplate::where('event_key', 'stock_transfer_requested_employee')
            ->update(['blade_view' => 'emails.stock_transfer.does_not_exist']);

        $id = $this->raise(3);

        $this->assertSame('pending', StockTransfer::findOrFail($id)->status);
        $this->assertEquals(3, $this->stockOf($this->sender, $this->products[0])->blocked_stock);
    }
}
