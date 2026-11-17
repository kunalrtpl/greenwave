<?php

namespace Tests\Feature;

use App\Product;
use App\ProductInventory;
use App\ProductStock;
use App\ProductStockLedger;
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

    /** @test */
    public function the_same_batch_number_cannot_be_added_twice()
    {
        $batch = 'DUP-' . uniqid();

        $this->submit(['supplier_batch_no' => $batch])[1]->assertJson(['status' => true]);

        $second = $this->submit(['supplier_batch_no' => $batch])[1];

        $second->assertStatus(200)->assertJson(['status' => false]);
        $this->assertContains(
            'already been added',
            $second->json('errors.supplier_batch_no.0')
        );

        $this->assertSame(1, ProductInventory::where('supplier_batch_no', $batch)->count());
    }

    /** @test */
    public function a_rejected_duplicate_moves_no_stock()
    {
        $batch = 'DUP2-' . uniqid();
        $this->submit(['supplier_batch_no' => $batch, 'stock' => 10]);

        $stock = (float) ProductStock::where('product_id', $this->product->id)->firstOrFail()->total_stock;
        $lines = ProductStockLedger::forProduct($this->product->id)->count();

        $this->submit(['supplier_batch_no' => $batch, 'stock' => 999])[1]->assertJson(['status' => false]);

        $this->assertEquals($stock, (float) ProductStock::where('product_id', $this->product->id)->firstOrFail()->total_stock);
        $this->assertSame($lines, ProductStockLedger::forProduct($this->product->id)->count());
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
