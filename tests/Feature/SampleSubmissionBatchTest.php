<?php

namespace Tests\Feature;

use App\AuthToken;
use App\FreeSamplingStock;
use App\Product;
use App\SampleSubmission;
use App\SamplingDispatch;
use App\Services\EmployeeBatchStockService;
use App\Services\SamplingDispatchService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MakesRfdBatches;
use Tests\TestCase;

/**
 * Samples handed to a customer come out of a named batch.
 *
 * Every movement goes through App\Services\SampleSubmissionService, so what a
 * submission takes out of the employee's stock and what deleting it gives back
 * are the same arithmetic, and the employee's per-batch holding keeps adding
 * up to their stock.
 */
class SampleSubmissionBatchTest extends TestCase
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

    /* ─────────────────────────── Submitting ─────────────────────────── */

    /** @test */
    public function a_submission_takes_the_qty_out_of_the_batch_it_names()
    {
        $batch = $this->hold(30, 'SS-1001');

        $this->submit(['qty' => 4, 'batch_ref' => $this->ref($batch)])
            ->assertStatus(200)->assertJson(['status' => true]);

        $submission = SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $this->assertSame($this->ref($batch), $submission->batch_ref);
        $this->assertSame('SS-1001', $submission->batch_no, 'the number is kept beside the reference');
        $this->assertEquals(4, $submission->qty);

        $this->assertEquals(26, $this->inHand(), 'it has left their stock');
        $this->assertEquals(26, $this->qtyInBatch($batch), 'and left that batch');
    }

    /** @test */
    public function a_submission_without_a_batch_is_refused()
    {
        $this->hold(30, 'SS-NONE');

        $this->submit(['qty' => 4])->assertStatus(422);

        $this->assertSame(0, SampleSubmission::where('user_id', $this->employee->id)
            ->where('submission_date', date('Y-m-d'))->count());
        $this->assertEquals(30, $this->inHand(), 'no stock moved');
    }

    /** @test */
    public function more_than_is_held_of_that_batch_is_refused()
    {
        $first  = $this->hold(5, 'SS-SMALL');
        $second = $this->hold(40, 'SS-BIG');

        // 45 Kg in hand overall, but only 5 Kg of the batch that was named.
        $this->submit(['qty' => 12, 'batch_ref' => $this->ref($first)])->assertStatus(422);

        $this->assertEquals(45, $this->inHand(), 'nothing moved');
        $this->assertEquals(5, $this->qtyInBatch($first));
        $this->assertEquals(40, $this->qtyInBatch($second));
    }

    /** @test */
    public function a_batch_of_another_product_is_refused()
    {
        $this->hold(30, 'SS-MINE');

        $other = Product::where('status', 1)->where('id', '!=', $this->product->id)->firstOrFail();
        $wrong = $this->rfdBatch($other->id, 50, 'SS-OTHER');

        $this->submit(['qty' => 2, 'batch_ref' => 'inventory:' . $wrong->id])->assertStatus(422);

        $this->assertEquals(30, $this->inHand());
    }

    /** @test */
    public function editing_a_submission_moves_only_the_difference()
    {
        $batch = $this->hold(30, 'SS-EDIT');

        $this->submit(['qty' => 4, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $submission = SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $this->assertEquals(26, $this->inHand());

        $this->submit([
            'qty' => 6,
            'batch_ref' => $this->ref($batch),
            'sample_submission_id' => $submission->id,
        ])->assertStatus(200);

        $this->assertEquals(24, $this->inHand(), 'only the extra 2 Kg left the stock');
        $this->assertEquals(6, $submission->fresh()->qty);
        $this->assertEquals(24, $this->qtyInBatch($batch));
    }

    /** @test */
    public function an_edit_may_use_the_qty_it_already_took_out()
    {
        // The whole batch goes out, then the submission is edited up — which
        // is only possible because what it already took is its own to use.
        $batch = $this->hold(10, 'SS-ALL');

        $this->submit(['qty' => 10, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $submission = SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $this->assertEquals(0, $this->inHand());

        $this->submit([
            'qty' => 11,
            'batch_ref' => $this->ref($batch),
            'sample_submission_id' => $submission->id,
        ])->assertStatus(422, 'one Kg more than the batch ever held');

        $this->submit([
            'qty' => 8,
            'batch_ref' => $this->ref($batch),
            'sample_submission_id' => $submission->id,
        ])->assertStatus(200);

        $this->assertEquals(2, $this->inHand());
    }

    /* ─────────────────────────── Deleting ─────────────────────────── */

    /** @test */
    public function deleting_a_submission_gives_the_stock_back_to_its_batch()
    {
        $batch = $this->hold(30, 'SS-DEL');

        $this->submit(['qty' => 7, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $submission = SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $this->assertEquals(23, $this->inHand());

        $this->postJson('/api/executive/delete-sample-submission',
            ['sample_submission_id' => $submission->id], $this->headers())
            ->assertStatus(200)->assertJson(['status' => true]);

        $this->assertNull(SampleSubmission::find($submission->id));
        $this->assertEquals(30, $this->inHand(), 'all of it is back');
        $this->assertEquals(30, $this->qtyInBatch($batch), 'back in the batch it came from');
    }

    /** @test */
    public function deleting_after_a_partial_return_gives_back_only_what_was_still_out()
    {
        $batch = $this->hold(30, 'SS-RET');

        $this->submit(['qty' => 10, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $submission = SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $this->postJson('/api/executive/return-sample-submission',
            ['sample_submission_id' => $submission->id, 'return_qty' => 4], $this->headers())
            ->assertStatus(200);

        $this->assertEquals(24, $this->inHand(), '4 Kg of the 10 came back');
        $this->assertEquals(24, $this->qtyInBatch($batch), 'and back into its batch');

        $this->postJson('/api/executive/delete-sample-submission',
            ['sample_submission_id' => $submission->id], $this->headers())->assertStatus(200);

        $this->assertEquals(30, $this->inHand(), 'only the 6 Kg still out was restored');
    }

    /** @test */
    public function a_submission_someone_else_owns_cannot_be_deleted()
    {
        $batch = $this->hold(10, 'SS-THEIRS');
        $this->submit(['qty' => 2, 'batch_ref' => $this->ref($batch)])->assertStatus(200);
        $submission = SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();

        $other = User::marketingEmployees()->where('users.id', '!=', $this->employee->id)->firstOrFail();

        $this->postJson('/api/executive/delete-sample-submission',
            ['sample_submission_id' => $submission->id],
            ['Authorization' => 'Bearer ' . $this->tokenFor($other), 'Accept' => 'application/json'])
            ->assertStatus(403);

        $this->assertNotNull(SampleSubmission::find($submission->id));
        $this->assertEquals(8, $this->inHand(), 'nothing was given back');
    }

    /** @test */
    public function the_batches_still_add_up_to_what_the_app_shows_them()
    {
        $first  = $this->hold(20, 'SS-SUM-A');
        $second = $this->hold(10, 'SS-SUM-B');

        $this->submit(['qty' => 5, 'batch_ref' => $this->ref($first)])->assertStatus(200);

        $row = collect($this->getJson('/api/executive/stock-transfers/stock', $this->headers())
            ->assertStatus(200)->json('data.stock'))
            ->firstWhere('product_id', $this->product->id);

        $this->assertEquals(25, $row['available_qty']);
        $this->assertEquals(0, $row['unbatched_qty']);
        $this->assertEquals($row['available_qty'], collect($row['batches'])->sum('available_qty'));

        $byBatch = collect($row['batches'])->keyBy('batch_no');
        $this->assertEquals(15, $byBatch['SS-SUM-A']['available_qty']);
        $this->assertEquals(10, $byBatch['SS-SUM-B']['available_qty']);

        $this->assertEquals(10, $this->qtyInBatch($second), 'the other batch is untouched');
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

    private function submit(array $data)
    {
        return $this->postJson('/api/executive/sample-submission', $data + [
            'submission_date' => date('Y-m-d'),
            'product_id'      => $this->product->id,
            'purpose'         => 'Trial',
            'submission_type' => 'Free',
            'complaint_id'    => null,
            'from'            => 'Free Samples',
            'sample_value'    => 0,
            'remarks'         => 'Batch test',
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
