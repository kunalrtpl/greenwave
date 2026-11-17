<?php

namespace Tests\Feature;

use App\AuthToken;
use App\FreeSamplingStock;
use App\Product;
use App\SampleStockAdjustment;
use App\SamplingDispatch;
use App\Services\EmployeeBatchStockService;
use App\Services\SampleStockAdjustmentService;
use App\Services\SamplingDispatchService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MakesRfdBatches;
use Tests\TestCase;

/**
 * Writing stock off comes out of a named batch, the same as a sample handed
 * to a customer, so the employee's per-batch holding keeps adding up to their
 * stock whichever way material leaves them.
 */
class SampleStockAdjustmentBatchTest extends TestCase
{
    use DatabaseTransactions;
    use MakesRfdBatches;

    /** @var \App\User */
    private $employee;

    /** @var \App\Product */
    private $product;

    /** @var string */
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::marketingEmployees()->orderBy('users.id')->firstOrFail();
        $this->product  = Product::where('status', 1)->firstOrFail();
        $this->token    = $this->tokenFor($this->employee);

        Mail::fake();
    }

    /** @test */
    public function an_adjustment_takes_the_qty_off_the_batch_it_names()
    {
        $batch = $this->hold(20, 'ADJ-1001');

        $this->adjust(['qty' => 3, 'batch_ref' => $this->ref($batch)])
            ->assertStatus(200)->assertJson(['status' => true]);

        $adjustment = SampleStockAdjustment::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $this->assertSame($this->ref($batch), $adjustment->batch_ref);
        $this->assertSame('ADJ-1001', $adjustment->batch_no);
        $this->assertEquals(3, $adjustment->qty);

        $this->assertEquals(17, $this->inHand(), 'written off their stock');
        $this->assertEquals(17, $this->qtyInBatch($batch), 'and off that batch');
    }

    /** @test */
    public function an_adjustment_without_a_batch_is_refused()
    {
        $this->hold(20, 'ADJ-NONE');

        $this->adjust(['qty' => 3])->assertStatus(422);

        $this->assertSame(0, SampleStockAdjustment::where('user_id', $this->employee->id)
            ->where('adjustment_date', date('Y-m-d'))->count());
        $this->assertEquals(20, $this->inHand(), 'no stock moved');
    }

    /** @test */
    public function more_than_is_held_of_that_batch_is_refused()
    {
        $small = $this->hold(2, 'ADJ-SMALL');
        $big   = $this->hold(30, 'ADJ-BIG');

        // 32 Kg in hand, but only 2 Kg of the batch that was named.
        $this->adjust(['qty' => 5, 'batch_ref' => $this->ref($small)])->assertStatus(422);

        $this->assertEquals(32, $this->inHand(), 'nothing moved');
        $this->assertEquals(2, $this->qtyInBatch($small));
        $this->assertEquals(30, $this->qtyInBatch($big));
    }

    /** @test */
    public function a_batch_of_another_product_is_refused()
    {
        $this->hold(20, 'ADJ-MINE');

        $other = Product::where('status', 1)->where('id', '!=', $this->product->id)->firstOrFail();
        $wrong = $this->rfdBatch($other->id, 50, 'ADJ-OTHER');

        $this->adjust(['qty' => 2, 'batch_ref' => 'inventory:' . $wrong->id])->assertStatus(422);

        $this->assertEquals(20, $this->inHand());
    }

    /** A negative qty is a write-off being undone: it puts stock back. */
    public function testANegativeAdjustmentPutsStockBackIntoTheBatch()
    {
        $batch = $this->hold(20, 'ADJ-NEG');

        $this->adjust(['qty' => 5, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $this->assertEquals(15, $this->qtyInBatch($batch));

        $this->adjust(['qty' => -5, 'batch_ref' => $this->ref($batch)])->assertStatus(200);

        $this->assertEquals(20, $this->inHand(), 'back where it was');
        $this->assertEquals(20, $this->qtyInBatch($batch));
    }

    /** @test */
    public function reverting_an_adjustment_gives_the_qty_back()
    {
        $batch = $this->hold(20, 'ADJ-REV');

        $this->adjust(['qty' => 4, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $adjustment = SampleStockAdjustment::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        app(SampleStockAdjustmentService::class)->revert($adjustment);

        $this->assertNull(SampleStockAdjustment::find($adjustment->id));
        $this->assertEquals(20, $this->inHand());
        $this->assertEquals(20, $this->qtyInBatch($batch));
    }

    /** @test */
    public function the_batches_still_add_up_after_a_write_off()
    {
        $first  = $this->hold(12, 'ADJ-SUM-A');
        $second = $this->hold(8, 'ADJ-SUM-B');

        $this->adjust(['qty' => 2, 'batch_ref' => $this->ref($first)])->assertStatus(200);

        $row = collect($this->getJson('/api/executive/stock-transfers/stock', $this->headers())
            ->assertStatus(200)->json('data.stock'))
            ->firstWhere('product_id', $this->product->id);

        $this->assertEquals(18, $row['available_qty']);
        $this->assertEquals(0, $row['unbatched_qty']);
        $this->assertEquals($row['available_qty'], collect($row['batches'])->sum('available_qty'));

        $byBatch = collect($row['batches'])->keyBy('batch_no');
        $this->assertEquals(10, $byBatch['ADJ-SUM-A']['available_qty']);
        $this->assertEquals(8, $byBatch['ADJ-SUM-B']['available_qty']);

        $this->assertEquals(8, $this->qtyInBatch($second), 'the other batch is untouched');
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** Give the employee $qty of a new batch, the way the flow really does. */
    private function hold($qty, $batchNo)
    {
        $batch = $this->rfdBatch($this->product->id, $qty + 50, $batchNo);

        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user',
            'dispatch_type'   => 'voluntary',
            'user_id'         => $this->employee->id,
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-' . uniqid(),
            'transport'       => 'Courier',
            'lr_no'           => 'LR-' . uniqid(),
            'voluntary_items' => [[
                'product_id'           => $this->product->id,
                'approved_pack_size'   => 1,
                'approved_no_of_packs' => $qty,
                'batch_ref'            => 'inventory:' . $batch->id,
                'dispatch_party_type'  => 'user',
                'dispatch_user_id'     => $this->employee->id,
            ]],
        ]);

        app(SamplingDispatchService::class)->markReceived(
            SamplingDispatch::where('batch_ref', 'inventory:' . $batch->id)->pluck('id')->all(),
            $this->employee->id
        );

        return $batch;
    }

    private function adjust(array $data)
    {
        return $this->postJson('/api/executive/sample-stock-adjustment', $data + [
            'product_id'      => $this->product->id,
            'amount'          => 0,
            'adjustment_date' => date('Y-m-d'),
            'reason'          => 'Material Expired',
            'remarks'         => 'Batch test',
            'other'           => '',
        ], $this->headers());
    }

    private function ref($batch)
    {
        return 'inventory:' . $batch->id;
    }

    private function inHand()
    {
        return (float) FreeSamplingStock::where('user_id', $this->employee->id)
            ->where('product_id', $this->product->id)
            ->value('stock_in_hand');
    }

    private function qtyInBatch($batch)
    {
        return app(EmployeeBatchStockService::class)
            ->qtyInBatch($this->employee->id, $this->product->id, $this->ref($batch));
    }

    private function tokenFor(User $user)
    {
        $token = encrypt('user##-' . $user->mobile . '-' . $user->id);
        AuthToken::create(['type' => 'user', 'user_id' => $user->id, 'auth_token' => $token]);

        return $token;
    }

    private function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
    }
}
