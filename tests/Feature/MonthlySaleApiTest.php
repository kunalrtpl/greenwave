<?php

namespace Tests\Feature;

use App\AuthToken;
use App\Customer;
use App\Dealer;
use App\MonthlySale;
use App\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Monthly sale — dealer app add / edit / delete / list. */
class MonthlySaleApiTest extends TestCase
{
    use DatabaseTransactions;

    private $dealer;
    private $customer;
    private $products;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::whereNotNull('dealer_id')
            ->whereHas('dealer', function ($q) { $q->where('status', 1); })
            ->first();
        if (!$this->customer) {
            $this->markTestSkipped('Needs a customer linked to an active dealer.');
        }
        $this->dealer = Dealer::find($this->customer->dealer_id);

        $this->products = Product::take(2)->get();
        if ($this->products->count() < 2) {
            $this->markTestSkipped('Needs two products.');
        }

        $this->token = encrypt('dealer##-' . $this->dealer->owner_mobile);
        AuthToken::create(['type' => 'dealer', 'dealer_id' => $this->dealer->id, 'auth_token' => $this->token]);
    }

    private function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
    }

    private function payload(array $overrides = [])
    {
        return array_merge([
            'month'       => 10,
            'year'        => 2026,
            'customer_id' => $this->customer->id,
            'remarks'     => 'test',
            'products'    => [
                ['product_id' => $this->products[0]->id, 'qty' => '12.125'],
                ['product_id' => $this->products[1]->id, 'qty' => 3],
            ],
        ], $overrides);
    }

    public function test_add_edit_list_delete()
    {
        $res = $this->postJson('/api/dealer/add-monthly-sale', $this->payload(), $this->headers());
        $res->assertStatus(200);
        $this->assertEquals(15.125, $res->json('data.monthly_sale.total_qty'));
        $id = $res->json('data.monthly_sale.id');

        $res = $this->postJson('/api/dealer/edit-monthly-sale', $this->payload([
            'monthly_sale_id' => $id,
            'month'           => 11,
            'products'        => [['product_id' => $this->products[1]->id, 'qty' => 1.5]],
        ]), $this->headers());
        $res->assertStatus(200)->assertJsonCount(1, 'data.monthly_sale.products');
        $this->assertSame(11, MonthlySale::find($id)->month);

        // Sept–Nov 2026 includes it; Sept–Oct does not.
        $this->postJson('/api/dealer/monthly-sales', ['from_month' => '09-2026', 'to_month' => '11-2026'], $this->headers())
            ->assertStatus(200)->assertJsonFragment(['id' => $id]);
        $ids = collect($this->postJson('/api/dealer/monthly-sales', ['from_month' => '09-2026', 'to_month' => '10-2026'], $this->headers())
            ->json('data.monthly_sales'))->pluck('id');
        $this->assertNotContains($id, $ids);

        $this->postJson('/api/dealer/delete-monthly-sale', ['monthly_sale_id' => $id], $this->headers())->assertStatus(200);
        $this->assertNull(MonthlySale::find($id));
    }

    public function test_list_defaults_to_current_month()
    {
        $res = $this->postJson('/api/dealer/monthly-sales', [], $this->headers())->assertStatus(200);
        $this->assertEquals(date('m-Y'), $res->json('data.from_month'));
        $this->assertEquals(date('m-Y'), $res->json('data.to_month'));
    }

    public function test_qty_more_than_three_decimals_is_rejected()
    {
        $this->postJson('/api/dealer/add-monthly-sale', $this->payload([
            'products' => [['product_id' => $this->products[0]->id, 'qty' => '1.1234']],
        ]), $this->headers())->assertStatus(422);
    }

    public function test_dealer_comes_from_the_token()
    {
        // A dealer_id in the body is ignored.
        $res = $this->postJson('/api/dealer/add-monthly-sale', $this->payload(['dealer_id' => 999999]), $this->headers());
        $res->assertStatus(200);
        $sale = MonthlySale::find($res->json('data.monthly_sale.id'));
        $this->assertEquals(Dealer::getParentDealer($this->dealer->toArray()), $sale->dealer_id);
        $this->assertEquals($this->dealer->id, $sale->created_by_dealer_id);
    }

    public function test_from_after_to_is_rejected()
    {
        $this->postJson('/api/dealer/monthly-sales', ['from_month' => '11-2026', 'to_month' => '09-2026'], $this->headers())
            ->assertStatus(422);
    }
}
