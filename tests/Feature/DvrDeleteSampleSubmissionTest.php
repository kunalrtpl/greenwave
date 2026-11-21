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
use App\UserDvr;
use App\UserDvrSampleSubmissionLink;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MakesRfdBatches;
use Tests\TestCase;

/**
 * POST /api/executive/v2/dvr/delete — the DVR's sample submissions go with it.
 *
 * Deleting a DVR used to remove only the link row, leaving the submission
 * behind holding stock the employee no longer had out. The submissions are now
 * deleted through App\Services\SampleSubmissionService, so the stock comes back
 * by exactly the same arithmetic as deleting a submission on its own.
 */
class DvrDeleteSampleSubmissionTest extends TestCase
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

    /* ─────────────────────────── Deleting ─────────────────────────── */

    /** @test */
    public function deleting_a_dvr_deletes_its_submission_and_gives_the_stock_back()
    {
        $batch = $this->hold(30, 'DVR-1001');

        $submission = $this->submission(8, $batch);

        $this->assertEquals(22, $this->inHand(), 'the sample has left their stock');
        $this->assertEquals(22, $this->qtyInBatch($batch), 'and left that batch');

        $dvr = $this->dvrWith([$submission]);

        $this->deleteDvr($dvr)->assertStatus(200)->assertJson(['status' => true]);

        $this->assertNull(SampleSubmission::find($submission->id), 'the submission went with the DVR');
        $this->assertEquals(30, $this->inHand(), 'the qty is back in their stock');
        $this->assertEquals(30, $this->qtyInBatch($batch), 'and back in the batch it came out of');
        $this->assertNull(UserDvr::find($dvr->id));
    }

    /** @test */
    public function every_submission_on_the_dvr_comes_back()
    {
        $batch = $this->hold(40, 'DVR-1002');

        $first  = $this->submission(5, $batch);
        $second = $this->submission(7, $batch);

        $this->assertEquals(28, $this->inHand());

        $this->deleteDvr($this->dvrWith([$first, $second]))->assertStatus(200);

        $this->assertNull(SampleSubmission::find($first->id));
        $this->assertNull(SampleSubmission::find($second->id));
        $this->assertEquals(40, $this->inHand(), 'both qtys are back');
        $this->assertEquals(40, $this->qtyInBatch($batch));
    }

    /**
     * Only what is still out comes back — a returned qty is already in stock,
     * so counting it again would hand the employee stock they never gave out.
     *
     * @test
     */
    public function a_returned_qty_is_not_given_back_twice()
    {
        $batch = $this->hold(30, 'DVR-1003');

        $submission = $this->submission(10, $batch);

        // The customer gave 4 Kg back, which the return already credited.
        $submission->is_returned = 1;
        $submission->return_qty  = 4;
        $submission->save();

        FreeSamplingStock::where('user_id', $this->employee->id)
            ->where('product_id', $this->product->id)
            ->increment('stock_in_hand', 4);

        $this->assertEquals(24, $this->inHand());

        $this->deleteDvr($this->dvrWith([$submission]))->assertStatus(200);

        $this->assertEquals(30, $this->inHand(), 'only the 6 Kg still out came back');
    }

    /** Older DVRs record the submission in the column the link table replaced. */
    /** @test */
    public function the_legacy_submission_column_is_handled_too()
    {
        $batch = $this->hold(30, 'DVR-1004');

        $submission = $this->submission(9, $batch);

        $dvr = $this->dvr();
        $dvr->sample_submission_id = $submission->id;
        $dvr->save();

        $this->deleteDvr($dvr)->assertStatus(200);

        $this->assertNull(SampleSubmission::find($submission->id));
        $this->assertEquals(30, $this->inHand());
    }

    /** @test */
    public function a_dvr_with_no_submissions_still_deletes()
    {
        $dvr = $this->dvr();

        $this->deleteDvr($dvr)->assertStatus(200);

        $this->assertNull(UserDvr::find($dvr->id));
    }

    /**
     * A submission recorded against another employee is left alone: deleting
     * this DVR must not move someone else's stock.
     *
     * @test
     */
    public function another_employees_submission_is_left_alone()
    {
        $batch = $this->hold(30, 'DVR-1005');

        $submission            = $this->submission(6, $batch);
        $submission->user_id   = $this->otherEmployee()->id;
        $submission->save();

        $before = $this->inHand();

        $dvr = $this->dvrWith([$submission]);

        $this->deleteDvr($dvr)->assertStatus(200);

        $this->assertNotNull(SampleSubmission::find($submission->id), 'it is not theirs to delete');
        $this->assertEquals($before, $this->inHand(), 'no stock moved');
        $this->assertSame(0, UserDvrSampleSubmissionLink::where('user_dvr_id', $dvr->id)->count(),
            'but the link to it is cleaned up');
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    private function deleteDvr(UserDvr $dvr)
    {
        return $this->postJson('/api/executive/v2/dvr/delete', ['id' => $dvr->id], $this->headers());
    }

    /** A DVR of this employee's, with nothing attached. */
    private function dvr()
    {
        $dvr = new UserDvr();
        $dvr->user_id  = $this->employee->id;
        $dvr->dvr_date = date('Y-m-d');
        $dvr->save();

        return $dvr;
    }

    /** A DVR of this employee's with these submissions linked to it. */
    private function dvrWith(array $submissions)
    {
        $dvr = $this->dvr();

        foreach ($submissions as $submission) {
            UserDvrSampleSubmissionLink::create([
                'user_dvr_id'          => $dvr->id,
                'user_id'              => $this->employee->id,
                'sample_submission_id' => $submission->id,
            ]);
        }

        return $dvr;
    }

    /** A sample handed to a customer out of this batch, posted as the app does. */
    private function submission($qty, $batch)
    {
        $this->postJson('/api/executive/sample-submission', [
            'submission_date' => date('Y-m-d'),
            'product_id'      => $this->product->id,
            'qty'             => $qty,
            'batch_ref'       => $this->ref($batch),
            'purpose'         => 'Trial',
            'submission_type' => 'Free',
            'from'            => 'Free Samples',
            'sample_value'    => 0,
            'remarks'         => 'DVR delete test',
        ], $this->headers())->assertStatus(200);

        return SampleSubmission::where('user_id', $this->employee->id)->latest('id')->firstOrFail();
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

    private function otherEmployee()
    {
        return User::marketingEmployees()
            ->where('users.id', '!=', $this->employee->id)
            ->orderBy('users.id')
            ->firstOrFail();
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
