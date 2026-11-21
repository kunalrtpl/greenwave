<?php

namespace Tests\Feature;

use App\Product;
use App\ProductInventory;
use App\ProductStock;
use App\ProductStockLedger;
use App\Services\ProductBatchService;
use App\Services\ProductStockService;
use App\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Add Incoming Material → product_stocks → product_stock_ledgers.
 *
 * Three things are held down here:
 *   - the form opens on Finished Product (RFD) and saves while the packing
 *     section is commented out,
 *   - a batch number can never be used twice,
 *   - every change of product_stocks leaves a credit / debit line behind, and
 *     products.current_stock keeps behaving exactly as it did before.
 */
class ProductStockLedgerTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $admin;

    /** @var \App\Product */
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('type', 'admin')->where('status', 1)->firstOrFail();
        $this->product = Product::where('status', 1)->firstOrFail();
    }

    private function submit(array $overrides = [])
    {
        $payload = array_merge([
            'incoming_date'     => date('Y-m-d'),
            'type'              => 'RFDM',
            'product_id'        => $this->product->id,
            'stock'             => 500,
            'supplier_batch_no' => 'TEST-' . uniqid(),
            'remarks'           => 'Received at plant gate',
        ], $overrides);

        return [$payload, $this->actingAs($this->admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('/admin/add-incoming-material', $payload)];
    }


    /* ──────────── What a batch is allowed to be used for ──────────── */

    /** Both boxes are ticked on the form, so an ordinary entry allows both. */
    /** @test */
    public function a_new_batch_is_allowed_for_both_by_default()
    {
        $batch = 'USE-' . uniqid();

        $this->submit([
            'supplier_batch_no'      => $batch,
            'used_in_sample'         => 1,
            'used_in_dispatch_order' => 1,
        ])[1]->assertJson(['status' => true]);

        $row = ProductInventory::where('supplier_batch_no', $batch)->firstOrFail();

        $this->assertSame(1, (int) $row->used_in_sample);
        $this->assertSame(1, (int) $row->used_in_dispatch_order);
    }

    /** An unticked box posts its hidden 0 and is stored as false. */
    /** @test */
    public function unticking_a_box_is_stored()
    {
        $batch = 'USE2-' . uniqid();

        $this->submit([
            'supplier_batch_no'      => $batch,
            'used_in_sample'         => 0,
            'used_in_dispatch_order' => 1,
        ])[1]->assertJson(['status' => true]);

        $row = ProductInventory::where('supplier_batch_no', $batch)->firstOrFail();

        $this->assertSame(0, (int) $row->used_in_sample);
        $this->assertSame(1, (int) $row->used_in_dispatch_order);
    }

    /** A payload that never mentions them keeps the column default. */
    /** @test */
    public function a_payload_without_the_boxes_still_allows_both()
    {
        $batch = 'USE3-' . uniqid();

        $this->submit(['supplier_batch_no' => $batch])[1]->assertJson(['status' => true]);

        $row = ProductInventory::where('supplier_batch_no', $batch)->firstOrFail();

        $this->assertSame(1, (int) $row->used_in_sample);
        $this->assertSame(1, (int) $row->used_in_dispatch_order);
    }

    /** @test */
    public function only_sample_marked_batches_are_offered_for_dispatch()
    {
        $allowed = 'USE-IN-' . uniqid();
        $held    = 'USE-OUT-' . uniqid();

        $this->submit(['supplier_batch_no' => $allowed, 'used_in_sample' => 1, 'used_in_dispatch_order' => 1]);
        $this->submit(['supplier_batch_no' => $held, 'used_in_sample' => 0, 'used_in_dispatch_order' => 1]);

        $offered = collect(app(ProductBatchService::class)->optionsForProduct($this->product->id))
            ->pluck('batch_no')
            ->all();

        $this->assertContains($allowed, $offered);
        $this->assertNotContains($held, $offered, 'a batch held back from sampling is not offered');
    }

    /**
     * Unticking a batch must not break what already went out on it: its
     * reference still resolves, so history and stock stay readable.
     *
     * @test
     */
    public function a_held_back_batch_can_still_be_resolved_by_reference()
    {
        $held = 'USE-REF-' . uniqid();

        $this->submit(['supplier_batch_no' => $held, 'used_in_sample' => 0]);

        $row = ProductInventory::where('supplier_batch_no', $held)->firstOrFail();
        $ref = 'inventory:' . $row->id;

        $found = app(ProductBatchService::class)->find([$ref]);

        $this->assertArrayHasKey($ref, $found);
        $this->assertFalse($found[$ref]->allows('sample'), 'but it says it is not for sampling');
        $this->assertTrue($found[$ref]->allows('dispatch_order'));
    }

    /** A route the source says nothing about is allowed. */
    /** @test */
    public function an_unknown_route_is_allowed()
    {
        $batch = 'USE-UNK-' . uniqid();

        $this->submit(['supplier_batch_no' => $batch, 'used_in_sample' => 0]);

        $row = ProductInventory::where('supplier_batch_no', $batch)->firstOrFail();
        $ref = 'inventory:' . $row->id;

        $this->assertTrue(app(ProductBatchService::class)->find([$ref])[$ref]->allows('export'));
    }

    /** Asks the form's live duplicate check about a batch number. */
    private function check($batchNo)
    {
        return $this->actingAs($this->admin)
            ->getJson('/admin/inventory/check-batch-no?type=RFDM&supplier_batch_no=' . urlencode($batchNo));
    }

    /** @test */
    public function the_type_dropdown_opens_on_finished_product_rfd()
    {
        $types = getMaterialTypes();

        $this->assertSame('RFDM', key($types), 'RFD must be the first option.');
        $this->assertSame('RFDM', defaultMaterialType());

        $html = $this->actingAs($this->admin)->get('/admin/add-incoming-material')->getContent();

        $this->assertContains('value="RFDM" selected', $html);
    }

    /** @test */
    public function the_packing_and_attachment_section_is_hidden_for_rfd()
    {
        $html = $this->actingAs($this->admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('/admin/append-material-details', ['type' => 'RFDM'])
            ->json('view');

        // Still asks for the product, qty and batch …
        $this->assertContains('name="product_id"', $html);
        $this->assertContains('name="supplier_batch_no"', $html);

        // … but the commented-out block renders nothing at all.
        $this->assertNotContains('PACKING DETAILS', $html);
        $this->assertNotContains('name="final_packing_type"', $html);
        $this->assertNotContains('name="packs_consumed"', $html);
        $this->assertNotContains('name="coa"', $html);
        $this->assertNotContains('name="qc_report"', $html);
    }

    /** @test */
    public function an_rfd_entry_saves_without_the_packing_section()
    {
        list($payload, $response) = $this->submit();

        $response->assertStatus(200)->assertJson(['status' => true]);

        $entry = ProductInventory::where('supplier_batch_no', $payload['supplier_batch_no'])->firstOrFail();

        $this->assertSame('RFDM', $entry->type);
        $this->assertEquals(500, $entry->stock);
        $this->assertEquals(0, $entry->packs_consumed);
        $this->assertEquals(0, $entry->labels_consumed);
    }

    /** @test */
    public function the_entry_credits_incoming_stock_and_total_stock()
    {
        $before = ProductStock::forProduct($this->product->id);
        $incomingBefore = (float) $before->incoming_stock;
        $totalBefore = (float) $before->total_stock;
        $productionBefore = (float) $before->production_stock;

        list($payload, $response) = $this->submit(['stock' => 250]);
        $response->assertJson(['status' => true]);

        $after = ProductStock::where('product_id', $this->product->id)->firstOrFail();

        $this->assertEquals($incomingBefore + 250, $after->incoming_stock);
        $this->assertEquals($totalBefore + 250, $after->total_stock);
        $this->assertEquals($productionBefore, $after->production_stock, 'Production stock is untouched.');
    }

    /** @test */
    public function the_entry_writes_a_credit_line_saying_where_it_came_from_and_who_added_it()
    {
        list($payload, $response) = $this->submit(['stock' => 120]);
        $response->assertJson(['status' => true]);

        $entry = ProductInventory::where('supplier_batch_no', $payload['supplier_batch_no'])->firstOrFail();
        $line = ProductStockLedger::forReference($entry)->firstOrFail();

        $this->assertSame(ProductStockLedger::ENTRY_CREDIT, $line->entry_type);
        $this->assertSame(ProductStock::BUCKET_INCOMING, $line->stock_type);
        $this->assertEquals(120, $line->qty);
        $this->assertEquals($line->stock_before + 120, $line->stock_after);
        $this->assertEquals($line->total_before + 120, $line->total_after);

        $this->assertSame(ProductStockService::SOURCE_INCOMING_MATERIAL, $line->source);
        $this->assertSame('Add Incoming Material', $line->source_label);
        $this->assertSame($payload['supplier_batch_no'], $line->batch_no);
        $this->assertSame((int) $this->admin->id, (int) $line->created_by);
        $this->assertSame($this->admin->name, $line->created_by_name);

        // The auto remark says what moved, from where, and by whom.
        $this->assertContains('Credited 120 Kg to Incoming Stock', $line->remarks);
        $this->assertContains('via Add Incoming Material', $line->remarks);
        $this->assertContains('Batch ' . $payload['supplier_batch_no'], $line->remarks);
        $this->assertContains('by ' . $this->admin->name, $line->remarks);

        // Whatever the user typed is kept separately from the auto remark.
        $this->assertSame('Received at plant gate', $line->notes);
    }

    /** @test */
    public function products_current_stock_keeps_working_the_way_it_did()
    {
        $before = (float) Product::find($this->product->id)->current_stock;

        $this->submit(['stock' => 60]);

        $this->assertEquals($before + 60, (float) Product::find($this->product->id)->current_stock);
    }

    /**
     * The same supplier batch legitimately arrives more than once, so a
     * finished-product entry is warned about a repeat rather than refused.
     * The warning is checkBatchNo()'s job; saving is not blocked.
     *
     * @test
     */
    public function the_same_batch_number_can_be_added_twice()
    {
        $batch = 'DUP-' . uniqid();

        $this->submit(['supplier_batch_no' => $batch])[1]->assertJson(['status' => true]);
        $this->submit(['supplier_batch_no' => $batch])[1]->assertJson(['status' => true]);

        $this->assertSame(2, ProductInventory::where('supplier_batch_no', $batch)->count());
    }

    /** Both entries are real stock, so both are credited. */
    /** @test */
    public function a_repeated_batch_number_still_moves_its_own_stock()
    {
        $batch = 'DUP2-' . uniqid();

        $this->submit(['supplier_batch_no' => $batch, 'stock' => 10]);

        $stock = (float) ProductStock::where('product_id', $this->product->id)->firstOrFail()->total_stock;
        $lines = ProductStockLedger::forProduct($this->product->id)->count();

        $this->submit(['supplier_batch_no' => $batch, 'stock' => 15])[1]->assertJson(['status' => true]);

        $this->assertEquals(
            $stock + 15,
            (float) ProductStock::where('product_id', $this->product->id)->firstOrFail()->total_stock
        );
        $this->assertSame($lines + 1, ProductStockLedger::forProduct($this->product->id)->count());
    }

    /** The form is told about the repeat, which is what replaces refusing it. */
    /** @test */
    public function a_repeated_batch_number_is_reported_by_the_check()
    {
        $batch = 'DUP3-' . uniqid();

        $this->check($batch)->assertStatus(200)->assertJson(['used' => false]);

        $this->submit(['supplier_batch_no' => $batch]);

        $res = $this->check($batch)->assertStatus(200);

        $this->assertTrue($res->json('used'));
        $this->assertContains('has already been used', $res->json('message'));
    }

    /** @test */
    public function a_batch_number_is_required()
    {
        $response = $this->submit(['supplier_batch_no' => ''])[1];

        $response->assertJson(['status' => false]);
        $this->assertContains('required', strtolower($response->json('errors.supplier_batch_no.0')));
    }

    /** @test */
    public function the_service_debits_and_keeps_the_total_in_step()
    {
        ProductStockService::credit($this->product->id, 100, [
            'bucket' => ProductStock::BUCKET_PRODUCTION,
            'source' => ProductStockService::SOURCE_PRODUCTION,
            'user'   => $this->admin,
        ]);

        $afterCredit = ProductStock::where('product_id', $this->product->id)->firstOrFail();
        $production = (float) $afterCredit->production_stock;

        $line = ProductStockService::debit($this->product->id, 40, [
            'bucket' => ProductStock::BUCKET_PRODUCTION,
            'source' => ProductStockService::SOURCE_ADJUSTMENT,
            'reason' => 'spillage',
            'user'   => $this->admin,
        ]);

        $after = ProductStock::where('product_id', $this->product->id)->firstOrFail();

        $this->assertSame(ProductStockLedger::ENTRY_DEBIT, $line->entry_type);
        $this->assertEquals($production - 40, $after->production_stock);
        $this->assertEquals(
            round($after->production_stock + $after->incoming_stock, 3),
            round($after->total_stock, 3)
        );
        $this->assertContains('Debited 40 Kg from Production Stock', $line->remarks);
        $this->assertContains('spillage', $line->remarks);
        $this->assertSame('-40 Kg', $line->signed_qty);
    }

    /** @test */
    public function a_debit_bigger_than_the_balance_is_refused()
    {
        $balance = ProductStockService::balance($this->product->id);

        try {
            ProductStockService::debit($this->product->id, $balance->incoming_stock + 5000, [
                'bucket' => ProductStock::BUCKET_INCOMING,
                'user'   => $this->admin,
            ]);
            $this->fail('Overdrawing the stock should have been refused.');
        } catch (Exception $e) {
            $this->assertContains('Not enough', $e->getMessage());
        }

        $this->assertEquals(
            (float) $balance->incoming_stock,
            (float) ProductStock::where('product_id', $this->product->id)->firstOrFail()->incoming_stock
        );
    }

    /** @test */
    public function a_reversal_puts_the_stock_back_and_says_so()
    {
        $credit = ProductStockService::credit($this->product->id, 75, [
            'bucket'       => ProductStock::BUCKET_INCOMING,
            'source'       => ProductStockService::SOURCE_INCOMING_MATERIAL,
            'batch_no'     => 'REV-' . uniqid(),
            'user'         => $this->admin,
        ]);

        $reversal = ProductStockService::reverse($credit, ['user' => $this->admin]);

        $this->assertSame(ProductStockLedger::ENTRY_DEBIT, $reversal->entry_type);
        $this->assertEquals($credit->stock_before, $reversal->stock_after);
        $this->assertSame($credit->batch_no, $reversal->batch_no);
        $this->assertContains('reversal of ledger #' . $credit->id, $reversal->remarks);
    }
}
