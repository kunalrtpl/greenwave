<?php

namespace Tests\Feature;

use App\AuthToken;
use App\FreeSamplingStock;
use App\Product;
use App\SamplingDispatch;
use App\Services\EmployeeBatchStockService;
use App\Services\SamplingDispatchService;
use App\Trial;
use App\User;
use App\UserDvr;
use App\UserDvrProduct;
use App\UserDvrTrialLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MakesRfdBatches;
use Tests\TestCase;

/**
 * POST /api/executive/v3/dvr/trial/add — product used in a trial comes out of
 * a named batch.
 *
 * Every V3 trial line now consumes the employee's stock, so batch_ref is
 * mandatory and the old own_stock flag decides nothing. All movement goes
 * through App\Services\TrialStockService, so what a trial takes out and what
 * deleting it gives back are the same arithmetic, and the employee's per-batch
 * holding keeps adding up to their stock.
 */
class TrialBatchTest extends TestCase
{
    use DatabaseTransactions;
    use MakesRfdBatches;

    /** @var \App\User */
    private $employee;

    /** @var \App\Product */
    private $product;

    /** @var string */
    private $token;

    /** @var \App\UserDvr */
    private $dvr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::marketingEmployees()->orderBy('users.id')->firstOrFail();
        $this->product  = Product::where('status', 1)->firstOrFail();
        $this->token    = $this->tokenFor($this->employee);
        $this->dvr      = $this->dvr();

        Mail::fake();
    }

    /* ─────────────────────────── Adding ─────────────────────────── */

    /** @test */
    public function a_trial_takes_its_qty_out_of_the_batch_it_names()
    {
        $batch = $this->hold(30, 'TR-1001');

        $res = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 6, 'batch_ref' => $this->ref($batch)],
        ]);

        $res->assertStatus(200)->assertJson(['status' => true]);

        $line = UserDvrProduct::where('trial_id', $res->json('data.id'))->firstOrFail();

        $this->assertSame($this->ref($batch), $line->batch_ref);
        $this->assertSame('TR-1001', $line->batch_no, 'the number is kept beside the reference');
        $this->assertEquals(6, $line->qty);

        $this->assertEquals(24, $this->inHand(), 'it has left their stock');
        $this->assertEquals(24, $this->qtyInBatch($batch), 'and left that batch');
    }

    /** @test */
    public function a_line_without_a_batch_is_refused()
    {
        $this->hold(30, 'TR-NONE');

        $this->addTrial([['product_id' => $this->product->id, 'qty' => 6]])
            ->assertStatus(422);

        $this->assertEquals(30, $this->inHand(), 'no stock moved');
        $this->assertSame(0, Trial::where('user_id', $this->employee->id)
            ->whereHas('dvrs', function ($q) { $q->where('user_dvrs.id', $this->dvr->id); })
            ->count(), 'and no trial was left behind');
    }

    /** @test */
    public function a_line_with_no_qty_is_refused()
    {
        $batch = $this->hold(30, 'TR-ZERO');

        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 0, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(422);

        $this->assertEquals(30, $this->inHand());
    }

    /** @test */
    public function more_than_is_held_of_that_batch_is_refused()
    {
        $small = $this->hold(5, 'TR-SMALL');
        $big   = $this->hold(40, 'TR-BIG');

        // 45 Kg in hand overall, but only 5 Kg of the batch that was named.
        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 12, 'batch_ref' => $this->ref($small)],
        ])->assertStatus(422);

        $this->assertEquals(45, $this->inHand(), 'no stock moved');
        $this->assertEquals(5, $this->qtyInBatch($small));
        $this->assertEquals(40, $this->qtyInBatch($big));
    }

    /** @test */
    public function a_batch_of_another_product_is_refused()
    {
        $this->hold(30, 'TR-OTHER');

        $other = Product::where('status', 1)->where('id', '!=', $this->product->id)->firstOrFail();
        $wrong = $this->rfdBatch($other->id, 100, 'TR-WRONG');

        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 2, 'batch_ref' => $this->batchRef($wrong)],
        ])->assertStatus(422);

        $this->assertEquals(30, $this->inHand());
    }

    /**
     * The same batch twice in one payload is measured against what is left
     * after the first line, not against the opening holding twice over.
     *
     * @test
     */
    public function the_same_batch_twice_is_counted_once_over()
    {
        $batch = $this->hold(10, 'TR-TWICE');

        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 6, 'batch_ref' => $this->ref($batch)],
            ['product_id' => $this->product->id, 'qty' => 6, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(422);

        $this->assertEquals(10, $this->inHand(), 'the whole request was rolled back');
        $this->assertEquals(10, $this->qtyInBatch($batch));
    }

    /** @test */
    public function two_lines_that_both_fit_are_both_taken()
    {
        $batch = $this->hold(20, 'TR-BOTH');

        $res = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 6, 'batch_ref' => $this->ref($batch)],
            ['product_id' => $this->product->id, 'qty' => 4, 'batch_ref' => $this->ref($batch)],
        ]);

        $res->assertStatus(200);

        $this->assertEquals(10, $this->inHand());
        $this->assertEquals(10, $this->qtyInBatch($batch));
        $this->assertSame(2, UserDvrProduct::where('trial_id', $res->json('data.id'))->count());
    }

    /** own_stock decided whether stock moved; it no longer decides anything. */
    /** @test */
    public function own_stock_is_ignored_rather_than_rejected()
    {
        $batch = $this->hold(30, 'TR-FLAG');

        $res = $this->addTrial([[
            'product_id' => $this->product->id,
            'qty'        => 5,
            'batch_ref'  => $this->ref($batch),
            'own_stock'  => 0,
        ]]);

        $res->assertStatus(200);

        $this->assertEquals(25, $this->inHand(), 'the stock moved anyway');
        $this->assertSame(1, (int) UserDvrProduct::where('trial_id', $res->json('data.id'))->value('own_stock'),
            'and the deprecated flag is written as true');
    }

    /** @test */
    public function a_trial_with_no_products_is_still_allowed()
    {
        $this->hold(30, 'TR-EMPTY');

        $this->addTrial([])->assertStatus(200);

        $this->assertEquals(30, $this->inHand());
    }

    /* ─────────────────────────── Deleting ─────────────────────────── */

    /** @test */
    public function deleting_a_trial_gives_the_qty_back_to_its_batch()
    {
        $batch = $this->hold(30, 'TR-DEL');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 8, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        $this->assertEquals(22, $this->inHand());

        $this->postJson('/api/executive/v2/dvr/trial/delete', ['id' => $trialId], $this->headers())
            ->assertStatus(200);

        $this->assertNull(Trial::find($trialId));
        $this->assertEquals(30, $this->inHand(), 'the qty is back in their stock');
        $this->assertEquals(30, $this->qtyInBatch($batch), 'and back in the batch it came out of');
    }

    /** Rows written before batches were recorded still come back on delete. */
    /** @test */
    public function a_legacy_own_stock_line_is_still_restored()
    {
        $this->hold(30, 'TR-LEGACY');

        $trial = Trial::create([
            'user_id'      => $this->employee->id,
            'created_by'   => $this->employee->id,
            'trial_number' => 9999,
        ]);

        UserDvrProduct::create([
            'trial_id'   => $trial->id,
            'product_id' => $this->product->id,
            'qty'        => 7,
            'own_stock'  => 1,
        ]);

        FreeSamplingStock::where('user_id', $this->employee->id)
            ->where('product_id', $this->product->id)
            ->decrement('stock_in_hand', 7);

        $this->assertEquals(23, $this->inHand());

        $this->postJson('/api/executive/v2/dvr/trial/delete', ['id' => $trial->id], $this->headers())
            ->assertStatus(200);

        $this->assertEquals(30, $this->inHand());
    }

    /* ─────────────────────── Holdings keep adding up ─────────────────────── */

    /**
     * A trial is one more way stock leaves an employee, so it has to be netted
     * into the per-batch holding — otherwise the batches would keep offering
     * qty that has already been used up.
     *
     * @test
     */
    public function the_batches_still_add_up_to_what_they_hold()
    {
        $first  = $this->hold(12, 'TR-SUM-1');
        $second = $this->hold(8, 'TR-SUM-2');

        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 2, 'batch_ref' => $this->ref($first)],
        ])->assertStatus(200);

        $held = app(EmployeeBatchStockService::class)->holdings($this->employee->id, [$this->product->id]);
        $held = $held[$this->product->id];

        $this->assertEquals(10, $this->qtyInBatch($first));
        $this->assertEquals(8, $this->qtyInBatch($second));
        $this->assertEquals(18, $this->inHand());
        $this->assertEquals(18, $held['batched_qty'], 'the batches account for all of it');
        $this->assertEquals(0, $held['unbatched_qty']);
    }


    /* ──────────────── Refusals are logged, not silent ──────────────── */

    /**
     * A line with no batch moves nothing and saves nothing, so the log is the
     * only record that the app tried it.
     *
     * @test
     */
    public function a_line_with_no_batch_is_written_to_the_log()
    {
        $this->hold(30, 'TR-LOG-1');

        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('warning')
            ->once()
            ->with(
                \Mockery::pattern('/\[trial-stock\].*no stock moved/'),
                \Mockery::type('array')
            );

        $this->addTrial([['product_id' => $this->product->id, 'qty' => 6]])
            ->assertStatus(422);

        $this->assertEquals(30, $this->inHand(), 'no stock moved');
    }

    /** @test */
    public function more_than_is_held_is_written_to_the_log_too()
    {
        $batch = $this->hold(5, 'TR-LOG-2');

        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('warning')
            ->once()
            ->with(
                \Mockery::pattern('/\[trial-stock\].*no stock moved.*holding/'),
                \Mockery::type('array')
            );

        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 20, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(422);

        $this->assertEquals(5, $this->inHand());
    }

    /** @test */
    public function a_consumed_line_is_written_to_the_log()
    {
        $batch = $this->hold(30, 'TR-LOG-3');

        Log::shouldReceive('warning')->andReturnNull();
        Log::shouldReceive('info')
            ->once()
            ->with(
                \Mockery::pattern('/\[trial-stock\] consumed 3 Kg from batch TR-LOG-3/'),
                \Mockery::type('array')
            );

        $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 3, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200);
    }

    /* ──────────────── Deleting the DVR the trial sits on ──────────────── */

    /** @test */
    public function deleting_the_dvr_gives_its_trials_stock_back()
    {
        $batch = $this->hold(30, 'TR-DVR');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 9, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        $this->assertEquals(21, $this->inHand());

        $this->postJson('/api/executive/v2/dvr/delete', ['id' => $this->dvr->id], $this->headers())
            ->assertStatus(200);

        $this->assertNull(UserDvr::find($this->dvr->id));
        $this->assertNull(Trial::find($trialId), 'the trial went with the DVR');
        $this->assertSame(0, UserDvrProduct::where('trial_id', $trialId)->count());
        $this->assertEquals(30, $this->inHand(), 'the qty is back in their stock');
        $this->assertEquals(30, $this->qtyInBatch($batch), 'and back in the batch it came out of');
    }

    /**
     * A trial shown on another DVR too is not this DVR's to delete — only the
     * link goes, and the stock stays consumed because the trial still stands.
     *
     * @test
     */
    public function a_trial_shared_with_another_dvr_is_left_alone()
    {
        $batch = $this->hold(30, 'TR-SHARED');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 9, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        $other = $this->dvr();
        $link = new UserDvrTrialLink();
        $link->user_dvr_id = $other->id;
        $link->trial_id    = $trialId;
        $link->save();

        $this->postJson('/api/executive/v2/dvr/delete', ['id' => $this->dvr->id], $this->headers())
            ->assertStatus(200);

        $this->assertNotNull(Trial::find($trialId), 'the other DVR still shows it');
        $this->assertEquals(21, $this->inHand(), 'so the stock stays consumed');
        $this->assertSame(0, UserDvrTrialLink::where('user_dvr_id', $this->dvr->id)->count(),
            'but this DVR\'s link to it is gone');
    }


    /* ──────────── Updating the details, never the stock ──────────── */

    /** @test */
    public function the_details_update_and_no_stock_moves()
    {
        $batch = $this->hold(30, 'TR-UPD-1');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 7, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        $this->assertEquals(23, $this->inHand());

        $this->updateTrial([
            'id'                => $trialId,
            'objective'         => 'Revised objective',
            'remarks'           => 'Customer wants a second run',
            'feedback_customer' => 'Shade matched',
            'status'            => 'Completed',
        ])->assertStatus(200)->assertJson(['status' => true]);

        $trial = Trial::findOrFail($trialId);

        $this->assertSame('Revised objective', $trial->objective);
        $this->assertSame('Completed', $trial->status);
        $this->assertSame('Shade matched', $trial->feedback_customer);

        $this->assertEquals(23, $this->inHand(), 'no stock moved');
        $this->assertEquals(23, $this->qtyInBatch($batch));
    }

    /** @test */
    public function the_product_lines_survive_an_update()
    {
        $batch = $this->hold(30, 'TR-UPD-2');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 7, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        $this->updateTrial(['id' => $trialId, 'objective' => 'Still one line'])->assertStatus(200);

        $line = UserDvrProduct::where('trial_id', $trialId)->firstOrFail();

        $this->assertSame($this->ref($batch), $line->batch_ref, 'the line is untouched');
        $this->assertEquals(7, $line->qty);
        $this->assertSame(1, UserDvrProduct::where('trial_id', $trialId)->count());
    }

    /** A build posting the whole trial back still updates the details. */
    /** @test */
    public function a_products_key_is_ignored_rather_than_rejected()
    {
        $batch = $this->hold(30, 'TR-UPD-3');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 7, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('warning')
            ->once()
            ->with(
                \Mockery::pattern('/\[trial-stock\] products ignored on trial update/'),
                \Mockery::type('array')
            );

        $this->updateTrial([
            'id'        => $trialId,
            'objective' => 'Details changed',
            'products'  => [['product_id' => $this->product->id, 'qty' => 99, 'batch_ref' => $this->ref($batch)]],
        ])->assertStatus(200);

        $this->assertSame('Details changed', Trial::findOrFail($trialId)->objective);
        $this->assertEquals(23, $this->inHand(), 'the ignored line moved nothing');
        $this->assertEquals(7, (float) UserDvrProduct::where('trial_id', $trialId)->value('qty'),
            'and did not replace the real one');
    }

    /** @test */
    public function another_employees_trial_cannot_be_updated()
    {
        $batch = $this->hold(30, 'TR-UPD-4');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 7, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        Trial::where('id', $trialId)->update(['user_id' => $this->otherEmployee()->id]);

        $this->updateTrial(['id' => $trialId, 'objective' => 'Not mine'])->assertStatus(404);

        $this->assertNotSame('Not mine', Trial::findOrFail($trialId)->objective);
    }

    /** @test */
    public function the_server_owned_fields_cannot_be_overwritten()
    {
        $batch = $this->hold(30, 'TR-UPD-5');

        $trialId = $this->addTrial([
            ['product_id' => $this->product->id, 'qty' => 7, 'batch_ref' => $this->ref($batch)],
        ])->assertStatus(200)->json('data.id');

        $before = Trial::findOrFail($trialId);

        $this->updateTrial([
            'id'           => $trialId,
            'objective'    => 'Trying it on',
            'user_id'      => $this->otherEmployee()->id,
            'trial_number' => 99999,
            'created_by'   => $this->otherEmployee()->id,
        ])->assertStatus(200);

        $after = Trial::findOrFail($trialId);

        $this->assertSame('Trying it on', $after->objective, 'the details did change');
        $this->assertEquals($before->user_id, $after->user_id);
        $this->assertEquals($before->trial_number, $after->trial_number);
        $this->assertEquals($before->created_by, $after->created_by);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    private function updateTrial(array $payload)
    {
        return $this->postJson('/api/executive/v3/dvr/trial/update', $payload, $this->headers());
    }

    private function otherEmployee()
    {
        return User::marketingEmployees()
            ->where('users.id', '!=', $this->employee->id)
            ->orderBy('users.id')
            ->firstOrFail();
    }

    private function addTrial(array $products)
    {
        return $this->postJson('/api/executive/v3/dvr/trial/add', [
            'user_dvr_id' => $this->dvr->id,
            'trial_type'  => 'Official',
            'objective'   => 'Batch test',
            'status'      => 'Pending',
            'products'    => $products,
        ], $this->headers());
    }

    private function dvr()
    {
        $dvr = new UserDvr();
        $dvr->user_id  = $this->employee->id;
        $dvr->dvr_date = date('Y-m-d');
        $dvr->save();

        return $dvr;
    }

    /** Stock in this employee's hands, in the batch they received it in. */
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
