<?php

namespace Tests\Feature;

use App\Contracts\ProductBatchSource;
use App\Product;
use App\ProductInventory;
use App\ProductStock;
use App\ProductStockLedger;
use App\SamplingDispatch;
use App\SamplingRequest;
use App\Services\ProductBatchService;
use App\Services\ProductStockService;
use App\Services\SamplingRequestService;
use App\Support\ProductBatch;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\MakesRfdBatches;
use Tests\TestCase;

/**
 * Sample dispatch takes material out of a named batch.
 *
 * The rules this holds down:
 *   - a product with no batch in stock cannot be dispatched at all,
 *   - a batch can never give out more than it holds, counting every row of the
 *     same challan that draws on it,
 *   - what leaves is subtracted from the batch and written to the ledger,
 *   - products.current_stock is no longer touched by a dispatch,
 *   - where batches come from is a plug: product_inventories today, anything
 *     else tomorrow.
 */
class SampleDispatchBatchTest extends TestCase
{
    use DatabaseTransactions;
    use MakesRfdBatches;

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
        $this->products  = Product::where('status', 1)->take(2)->get();

        if ($this->products->count() < 2) {
            $this->markTestSkipped('Needs at least two active products.');
        }
    }

    /* ─────────────────────────── Listing ─────────────────────────── */

    /** @test */
    public function the_batch_list_holds_only_rfd_batches_that_still_have_stock()
    {
        $product = $this->products[0];

        $full  = $this->rfdBatch($product->id, 40);
        $empty = $this->rfdBatch($product->id, 0);

        // Sale return material is not dispatchable, whatever is left in it.
        $saleReturn = $this->rfdBatch($product->id, 25);
        $saleReturn->type = ProductInventory::TYPE_SALE_RETURN;
        $saleReturn->save();

        $refs = collect(app(ProductBatchService::class)->optionsForProduct($product->id))->pluck('ref');

        $this->assertTrue($refs->contains('inventory:' . $full->id));
        $this->assertFalse($refs->contains('inventory:' . $empty->id), 'an empty batch is not offered');
        $this->assertFalse($refs->contains('inventory:' . $saleReturn->id), 'only RFD material is dispatchable');
    }

    /** @test */
    public function the_screen_is_told_each_batchs_number_and_what_is_left()
    {
        $product = $this->products[0];
        $batch   = $this->rfdBatch($product->id, 12.5, 'BN-OPT');

        $option = collect(app(ProductBatchService::class)->optionsForProduct($product->id))
            ->firstWhere('ref', 'inventory:' . $batch->id);

        $this->assertSame('BN-OPT', $option['batch_no']);
        $this->assertEquals(12.5, $option['remaining_qty']);
        $this->assertSame('inventory', $option['source']);
        // Reads qty first, then which batch it is: "12.5 Kg (BN-OPT)".
        $this->assertSame('12.5 Kg (BN-OPT)', $option['label']);
    }

    /** @test */
    public function the_dispatch_screen_serves_the_batches_of_a_product()
    {
        $batch = $this->rfdBatch($this->products[0]->id, 7);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/sample-dispatch/batches?product_id=' . $this->products[0]->id)
            ->assertStatus(200);

        $this->assertContains('inventory:' . $batch->id, collect($response->json('batches'))->pluck('ref')->all());
    }

    /** @test */
    public function a_product_with_no_batch_is_served_an_empty_list()
    {
        ProductInventory::where('product_id', $this->products[1]->id)->update(['remaining_stock' => 0]);

        $this->actingAs($this->admin)
            ->getJson('/admin/sample-dispatch/batches?product_id=' . $this->products[1]->id)
            ->assertStatus(200)
            ->assertJson(['batches' => []]);
    }

    /** @test */
    public function the_request_list_carries_the_batches_of_each_line()
    {
        $line = $this->readyLine(2);
        $batch = $this->rfdBatch($line->product_id, 50);

        $rows = collect($this->actingAs($this->admin)
            ->getJson('/admin/sample-dispatch/requests?user_id=' . $this->executive->id)
            ->assertStatus(200)
            ->json('requests'))->keyBy('id');

        $this->assertEquals($line->product_id, $rows[$line->id]['product_id']);
        $this->assertContains('inventory:' . $batch->id, collect($rows[$line->id]['batches'])->pluck('ref')->all());
    }

    /* ─────────────────────────── Dispatching ─────────────────────────── */

    /** @test */
    public function a_dispatch_without_a_batch_is_refused()
    {
        $line = $this->readyLine(2);
        $this->rfdBatch($line->product_id, 50);

        $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2],
        ])->assertStatus(422)->assertJsonValidationErrors(['items.0.batch_ref']);

        $this->assertSame(0, SamplingDispatch::where('sampling_request_id', $line->id)->count());
        $this->assertSame('approved', $line->fresh()->status, 'nothing was dispatched');
    }

    /** @test */
    public function a_product_with_nothing_left_in_stock_cannot_be_dispatched()
    {
        $line = $this->readyLine(2);
        $this->rfdBatch($line->product_id, 0);   // a batch exists but is empty

        $this->assertSame([], app(ProductBatchService::class)->optionsForProduct($line->product_id),
            'an empty batch is not offered to the screen');

        $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_ref' => 'inventory:999999999'],
        ])->assertStatus(422)->assertJsonValidationErrors(['items.0.batch_ref']);
    }

    /** @test */
    public function dispatching_takes_the_qty_out_of_the_batch()
    {
        $line  = $this->readyLine(2);
        $batch = $this->rfdBatch($line->product_id, 10);

        $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_ref' => $this->batchRef($batch)],
        ])->assertStatus(200);

        $this->assertEquals(8, $batch->fresh()->remaining_stock);
        $this->assertEquals(10, $batch->fresh()->stock, 'what was received never changes');
    }

    /** @test */
    public function the_challan_carries_the_batchs_own_number()
    {
        $line  = $this->readyLine(2);
        $batch = $this->rfdBatch($line->product_id, 10, 'REAL-BN');

        $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2,
             'batch_ref' => $this->batchRef($batch), 'batch_no' => 'TYPED-RUBBISH'],
        ])->assertStatus(200);

        $this->assertSame('REAL-BN', SamplingDispatch::where('sampling_request_id', $line->id)->value('batch_no'));
    }

    /** @test */
    public function dispatching_writes_a_debit_to_the_ledger()
    {
        $line  = $this->readyLine(2);
        $batch = $this->rfdBatch($line->product_id, 10, 'LEDGER-BN');

        $before = (float) ProductStockService::balance($line->product_id)->incoming_stock;

        $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_ref' => $this->batchRef($batch)],
        ])->assertStatus(200);

        $dispatch = SamplingDispatch::where('sampling_request_id', $line->id)->firstOrFail();
        $entry    = ProductStockLedger::forReference($dispatch)->firstOrFail();

        $this->assertSame(ProductStockLedger::ENTRY_DEBIT, $entry->entry_type);
        $this->assertSame(ProductStock::BUCKET_INCOMING, $entry->stock_type);
        $this->assertEquals(2, $entry->qty);
        $this->assertSame('LEDGER-BN', $entry->batch_no);
        $this->assertSame(ProductStockService::SOURCE_SAMPLE_DISPATCH, $entry->source);
        $this->assertSame('Sample Dispatch', $entry->source_label);
        $this->assertSame($dispatch->dispatch_ref_no, $entry->reference_no);
        $this->assertContains('Debited 2 Kg from Incoming Stock', $entry->remarks);
        $this->assertContains($line->reference_no, $entry->remarks);

        $this->assertEquals($before - 2, (float) ProductStockService::balance($line->product_id)->incoming_stock);
    }

    /** @test */
    public function a_dispatch_no_longer_touches_products_current_stock()
    {
        $line  = $this->readyLine(2);
        $batch = $this->rfdBatch($line->product_id, 10);

        $before = (float) Product::find($line->product_id)->current_stock;

        $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_ref' => $this->batchRef($batch)],
        ])->assertStatus(200);

        $this->assertEquals($before, (float) Product::find($line->product_id)->current_stock);
    }

    /* ─────────────────────────── Stock checks ─────────────────────────── */

    /** @test */
    public function a_batch_cannot_give_out_more_than_it_holds()
    {
        $line  = $this->readyLine(5);
        $batch = $this->rfdBatch($line->product_id, 3, 'SHORT-BN');

        $response = $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 5, 'batch_ref' => $this->batchRef($batch)],
        ])->assertStatus(422);

        $this->assertContains('SHORT-BN', $this->errorFor($response, 'items.0.batch_ref'));
        $this->assertContains('3 Kg left', $this->errorFor($response, 'items.0.batch_ref'));
        $this->assertEquals(3, $batch->fresh()->remaining_stock, 'nothing left the batch');
    }

    /** @test */
    public function two_rows_on_one_challan_may_not_overdraw_the_same_batch()
    {
        // The same product twice, as "Add Product" allows, drawing on one batch.
        $first  = $this->readyLine(3);
        $second = $this->readyLine(3, $first->product_id);
        $batch  = $this->rfdBatch($first->product_id, 4, 'SHARED-BN');

        $response = $this->postDispatch([
            ['sampling_request_id' => $first->id,  'dispatch_qty' => 3, 'batch_ref' => $this->batchRef($batch)],
            ['sampling_request_id' => $second->id, 'dispatch_qty' => 3, 'batch_ref' => $this->batchRef($batch)],
        ])->assertStatus(422);

        // The second row is the one that does not fit.
        $message = $this->errorFor($response, 'items.1.batch_ref');
        $this->assertContains('SHARED-BN', $message);
        $this->assertContains('already being sent on this challan', $message);

        $this->assertEquals(4, $batch->fresh()->remaining_stock, 'the whole save was refused');
        $this->assertSame(0, SamplingDispatch::whereIn('sampling_request_id', [$first->id, $second->id])->count());
    }

    /** @test */
    public function two_rows_that_do_fit_both_come_out_of_the_same_batch()
    {
        $first  = $this->readyLine(2);
        $second = $this->readyLine(2, $first->product_id);
        $batch  = $this->rfdBatch($first->product_id, 10);

        $this->postDispatch([
            ['sampling_request_id' => $first->id,  'dispatch_qty' => 2, 'batch_ref' => $this->batchRef($batch)],
            ['sampling_request_id' => $second->id, 'dispatch_qty' => 2, 'batch_ref' => $this->batchRef($batch)],
        ])->assertStatus(200);

        $this->assertEquals(6, $batch->fresh()->remaining_stock);
        $this->assertSame(2, ProductStockLedger::where('batch_no', $batch->supplier_batch_no)->debits()->count());
    }

    /** @test */
    public function a_batch_of_another_product_is_refused()
    {
        $line  = $this->readyLine(2);
        $other = $this->rfdBatch($this->otherProductId($line->product_id), 50, 'WRONG-BN');

        $response = $this->postDispatch([
            ['sampling_request_id' => $line->id, 'dispatch_qty' => 2, 'batch_ref' => $this->batchRef($other)],
        ])->assertStatus(422);

        $this->assertContains('belongs to a different product', $this->errorFor($response, 'items.0.batch_ref'));
        $this->assertEquals(50, $other->fresh()->remaining_stock);
    }

    /* ─────────────────────── More sources later ─────────────────────── */

    /** @test */
    public function a_reference_names_its_source_so_more_can_be_added()
    {
        $this->assertSame(['source' => 'inventory', 'id' => '45'], ProductBatchService::parse('inventory:45'));
        $this->assertNull(ProductBatchService::parse('45'));
        $this->assertNull(ProductBatchService::parse(''));
    }

    /** @test */
    public function batches_from_a_second_source_are_offered_alongside_the_first()
    {
        $product = $this->products[0];
        $own     = $this->rfdBatch($product->id, 5);

        $service = (new ProductBatchService())->setSources([
            new \Tests\Feature\Fakes\MadeUpBatchSource($product->id),
            new \App\Services\BatchSources\ProductInventoryBatchSource(),
        ]);

        $refs = collect($service->optionsForProduct($product->id))->pluck('ref');

        $this->assertTrue($refs->contains('madeup:1'), 'a new source shows up with no other change');
        $this->assertTrue($refs->contains('inventory:' . $own->id), 'and the old one keeps working');
        $this->assertNotEmpty($service->lock(['madeup:1']), 'and its batches resolve by reference');
    }

    /* ────────── Batches held back from sampling ────────── */

    /**
     * Add Incoming Material records whether a batch may go out as a sample.
     * One that is not marked for it is never offered on the dispatch screen.
     *
     * @test
     */
    public function a_batch_not_marked_for_sampling_is_not_offered()
    {
        $product = $this->products[0];

        $offered = $this->rfdBatch($product->id, 40, 'SMP-IN-' . uniqid());

        $held = $this->rfdBatch($product->id, 40, 'SMP-OUT-' . uniqid());
        $held->used_in_sample = 0;
        $held->save();

        $refs = collect(app(ProductBatchService::class)->optionsForProduct($product->id))->pluck('ref');

        $this->assertTrue($refs->contains('inventory:' . $offered->id));
        $this->assertFalse($refs->contains('inventory:' . $held->id));
    }

    /**
     * The dropdown is the gate, but a posted reference is checked too: the
     * mark can be cleared while a form is open.
     *
     * @test
     */
    public function dispatching_out_of_a_held_back_batch_is_refused()
    {
        $product = $this->products[0];
        $line    = $this->readyLine(5, $product->id);

        $held = $this->rfdBatch($product->id, 40, 'SMP-POST-' . uniqid());
        $held->used_in_sample = 0;
        $held->save();

        $response = $this->postDispatch([[
            'sampling_request_id' => $line->id,
            'dispatch_qty'        => 5,
            'batch_ref'           => 'inventory:' . $held->id,
        ]]);

        $response->assertStatus(422);
        $this->assertContains('not marked for use in samples', $this->errorFor($response, 'items.0.batch_ref'));

        $this->assertEquals(40, (float) $held->fresh()->remaining_stock, 'nothing left the batch');
    }

    /** Clearing the mark later must not disturb what already went out. */
    /** @test */
    public function a_batch_already_dispatched_still_resolves_after_being_held_back()
    {
        $product = $this->products[0];
        $line    = $this->readyLine(5, $product->id);
        $batch   = $this->rfdBatch($product->id, 40, 'SMP-WAS-' . uniqid());

        $this->postDispatch([[
            'sampling_request_id' => $line->id,
            'dispatch_qty'        => 5,
            'batch_ref'           => 'inventory:' . $batch->id,
        ]])->assertStatus(200);

        $batch->used_in_sample = 0;
        $batch->save();

        $ref   = 'inventory:' . $batch->id;
        $found = app(ProductBatchService::class)->find([$ref]);

        $this->assertArrayHasKey($ref, $found, 'the dispatched batch is still readable');
        $this->assertEquals(35, $found[$ref]->remainingQty);
    }

    /* ─────────────────────────── Helpers ─────────────────────────── */

    /** An approved, ready request line of qty Kg. */
    private function readyLine($qty, $productId = null)
    {
        $productId = $productId ?: $this->products[0]->id;

        $line = app(SamplingRequestService::class)->createFromApi([
            'sample_type' => 'free',
            'items' => [[
                'product_id'  => $productId,
                'pack_size'   => 1,
                'no_of_packs' => $qty,
                'qty'         => $qty,
            ]],
            'purpose_reason_for_request' => 'Batch tests',
            'dispatch_address'           => "Test\nSomewhere",
        ], $this->executive->id)->first();

        $service = app(SamplingRequestService::class);
        $service->approve($line->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => $qty]);
        $service->markReady($line->fresh(), []);

        return $line->fresh();
    }

    /** Validation keys carry dots, so they are read off the map rather than traversed. */
    private function errorFor($response, $key)
    {
        $errors = $response->json('errors');

        return isset($errors[$key]) ? $errors[$key][0] : '';
    }

    private function otherProductId($productId)
    {
        return $this->products->firstWhere('id', '!=', $productId)->id;
    }

    private function postDispatch(array $items)
    {
        return $this->actingAs($this->admin)->postJson('/admin/sample-dispatch', [
            'answerable_type' => 'user',
            'dispatch_type'   => 'request',
            'user_id'         => $this->executive->id,
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-' . uniqid(),
            'invoice_date'    => date('Y-m-d'),
            'transport'       => 'Courier',
            'lr_no'           => 'LR-' . uniqid(),
            'items'           => $items,
        ]);
    }
}
