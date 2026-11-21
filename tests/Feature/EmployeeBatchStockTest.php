<?php

namespace Tests\Feature;

use App\AuthToken;
use App\Product;
use App\SamplingDispatch;
use App\Services\EmployeeBatchStockService;
use App\Services\SamplingDispatchService;
use App\StockTransfer;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MakesRfdBatches;
use Tests\TestCase;

/**
 * What an employee is holding, batch by batch.
 *
 * The number the app shows an employee is their own stock, so the batches
 * listed beside it must be their own too — how much of each batch they are
 * holding, not how much of it is left in stores. These add up to
 * available_qty, which is the thing that makes the screen read correctly.
 */
class EmployeeBatchStockTest extends TestCase
{
    use DatabaseTransactions;
    use MakesRfdBatches;

    /** @var \App\User */
    private $employee;

    /** @var \App\User */
    private $other;

    /** @var \App\Product */
    private $product;

    /** @var string */
    private $token;

    /** @var string */
    private $otherToken;

    protected function setUp(): void
    {
        parent::setUp();

        $employees = User::marketingEmployees()->orderBy('users.id')->take(2)->get();
        if ($employees->count() < 2) {
            $this->markTestSkipped('Needs two marketing employees.');
        }

        $this->employee = $employees[0];
        $this->other    = $employees[1];
        $this->product  = Product::where('status', 1)->firstOrFail();

        $this->token      = $this->tokenFor($this->employee);
        $this->otherToken = $this->tokenFor($this->other);

        Mail::fake();
    }

    /* ─────────────────────────── The real case ─────────────────────────── */

    /**
     * 50 Kg out of one batch and 40 out of another is 90 Kg in hand — and the
     * two batches say 50 and 40, not what is left of them in stores.
     */
    public function testTheBatchesAreWhatTheEmployeeHoldsNotWhatIsLeftInStores()
    {
        $first  = $this->rfdBatch($this->product->id, 100, 'B-1001');
        $second = $this->rfdBatch($this->product->id, 50, 'B-1002');

        $this->dispatchToEmployee([$first->id => 50, $second->id => 40]);

        $stock = $this->stockRow();

        $this->assertEquals(90, $stock['available_qty']);
        $this->assertSame(2, $stock['batch_count']);
        $this->assertEquals(0, $stock['unbatched_qty']);

        $byBatch = collect($stock['batches'])->keyBy('batch_no');
        $this->assertEquals(50, $byBatch['B-1001']['available_qty']);
        $this->assertEquals(40, $byBatch['B-1002']['available_qty']);

        // What the employee holds adds up to what the app shows them.
        $this->assertEquals(
            $stock['available_qty'],
            collect($stock['batches'])->sum('available_qty')
        );

        // Stores still report their own remaining, which is a different number.
        $this->assertEquals(50, $first->fresh()->remaining_stock);
        $this->assertEquals(10, $second->fresh()->remaining_stock);
    }

    /** Material not yet confirmed received is not theirs to send on. */
    public function testOnlyReceivedMaterialIsCounted()
    {
        $batch = $this->rfdBatch($this->product->id, 100, 'B-PENDING');

        $this->dispatchToEmployee([$batch->id => 20], false);

        $this->assertSame([], app(EmployeeBatchStockService::class)
            ->batchesFor($this->employee->id, $this->product->id));
    }

    /** A batch is listed with where it came from, ready to be sent on. */
    public function testEachBatchSaysHowToReferToIt()
    {
        $batch = $this->rfdBatch($this->product->id, 100, 'B-REF');
        $this->dispatchToEmployee([$batch->id => 10]);

        $listed = collect($this->stockRow()['batches'])->firstWhere('batch_no', 'B-REF');

        $this->assertSame('inventory:' . $batch->id, $listed['batch_ref']);
        $this->assertSame('inventory', $listed['source']);
        $this->assertSame('Incoming Material (RFD)', $listed['source_label']);
        $this->assertSame('10 Kg (' . $listed['batch_no'] . ')', $listed['label']);
    }

    /* ─────────────────────────── Transfers ─────────────────────────── */

    /** Sending stock on takes it out of the batch it came from. */
    public function testTransferringOutReducesThatBatch()
    {
        $first  = $this->rfdBatch($this->product->id, 100, 'T-1001');
        $second = $this->rfdBatch($this->product->id, 100, 'T-1002');
        $this->dispatchToEmployee([$first->id => 50, $second->id => 40]);

        $id = $this->raiseTransfer($first->id, 20);

        // While it is waiting, the qty is already spoken for.
        $byBatch = collect($this->stockRow()['batches'])->keyBy('batch_no');
        $this->assertEquals(30, $byBatch['T-1001']['available_qty']);
        $this->assertEquals(40, $byBatch['T-1002']['available_qty']);

        $this->postJson('/api/executive/stock-transfers/' . $id . '/approve', [], $this->headers($this->otherToken))
            ->assertStatus(200);

        $byBatch = collect($this->stockRow()['batches'])->keyBy('batch_no');
        $this->assertEquals(30, $byBatch['T-1001']['available_qty'], 'it has gone for good');
        $this->assertEquals(70, $this->stockRow()['available_qty']);

        // And it is now the other employee's, in the same batch.
        $theirs = collect($this->stockRow($this->otherToken)['batches'])->keyBy('batch_no');
        $this->assertEquals(20, $theirs['T-1001']['available_qty']);
    }

    /** A refused transfer gives the batch its qty back. */
    public function testARefusedTransferReturnsTheQtyToItsBatch()
    {
        $batch = $this->rfdBatch($this->product->id, 100, 'R-1001');
        $this->dispatchToEmployee([$batch->id => 30]);

        $id = $this->raiseTransfer($batch->id, 10);
        $this->assertEquals(20, $this->qtyInBatch($batch));

        $this->postJson('/api/executive/stock-transfers/' . $id . '/decline',
            ['decline_reason' => 'Not needed'], $this->headers($this->otherToken))->assertStatus(200);

        $this->assertEquals(30, $this->qtyInBatch($batch), 'back where it was');
    }

    /** They may not send more of a batch than they are holding of it. */
    public function testSendingMoreOfABatchThanIsHeldIsRefused()
    {
        $first  = $this->rfdBatch($this->product->id, 100, 'X-1001');
        $second = $this->rfdBatch($this->product->id, 100, 'X-1002');
        $this->dispatchToEmployee([$first->id => 10, $second->id => 40]);

        // 50 Kg in hand overall, but only 10 Kg of this batch.
        $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $this->other->id,
            'items'      => [['product_id' => $this->product->id, 'qty' => 25,
                              'batch_ref' => 'inventory:' . $first->id]],
        ], $this->headers($this->token))->assertStatus(422);

        $this->assertSame(0, StockTransfer::where('from_user_id', $this->employee->id)->count());
        $this->assertEquals(10, $this->qtyInBatch($first), 'nothing was blocked');
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** Voluntary dispatch of each batch's qty to the employee, then received. */
    private function dispatchToEmployee(array $qtyByBatchId, $received = true)
    {
        $items = [];
        foreach ($qtyByBatchId as $batchId => $qty) {
            $items[] = [
                'product_id'           => $this->product->id,
                'approved_pack_size'   => 1,
                'approved_no_of_packs' => $qty,
                'batch_ref'            => 'inventory:' . $batchId,
                'dispatch_party_type'  => 'user',
                'dispatch_user_id'     => $this->employee->id,
            ];
        }

        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user',
            'dispatch_type'   => 'voluntary',
            'user_id'         => $this->employee->id,
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-' . uniqid(),
            'transport'       => 'Courier',
            'lr_no'           => 'LR-' . uniqid(),
            'voluntary_items' => $items,
        ]);

        if (!$received) {
            return;
        }

        $ids = SamplingDispatch::whereIn('batch_ref', array_map(function ($id) {
            return 'inventory:' . $id;
        }, array_keys($qtyByBatchId)))->pluck('id')->all();

        app(SamplingDispatchService::class)->markReceived($ids, $this->employee->id);
    }

    private function raiseTransfer($batchId, $qty)
    {
        return $this->postJson('/api/executive/stock-transfers', [
            'to_user_id' => $this->other->id,
            'items'      => [['product_id' => $this->product->id, 'qty' => $qty,
                              'batch_ref' => 'inventory:' . $batchId]],
        ], $this->headers($this->token))->assertStatus(200)->json('data.stock_transfer.id');
    }

    private function qtyInBatch($batch)
    {
        return app(EmployeeBatchStockService::class)
            ->qtyInBatch($this->employee->id, $this->product->id, 'inventory:' . $batch->id);
    }

    private function stockRow($token = null)
    {
        return collect($this->getJson('/api/executive/stock-transfers/stock',
                $this->headers($token ?: $this->token))
            ->assertStatus(200)->json('data.stock'))
            ->firstWhere('product_id', $this->product->id);
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
}
