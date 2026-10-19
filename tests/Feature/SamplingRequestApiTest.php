<?php

namespace Tests\Feature;

use App\AuthToken;
use App\Product;
use App\SamplingRequest;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The executive app's entry point into the revamped sampling module:
 * POST /api/executive/create-sampling-request.
 *
 * The legacy /create-sample-request endpoint is deliberately untouched and
 * still writes the old samplings tables, so app builds already in the field
 * keep working — that is asserted here too.
 */
class SamplingRequestApiTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $executive;

    /** @var string */
    private $token;

    /** @var \Illuminate\Support\Collection */
    private $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->executive = User::where('status', 1)->where('app_access', 'Yes')->firstOrFail();
        $this->products  = Product::where('status', 1)->take(4)->get();

        if ($this->products->count() < 4) {
            $this->markTestSkipped('Needs at least four active products.');
        }

        $this->token = encrypt('user##-' . $this->executive->mobile);
        AuthToken::create([
            'type'       => 'user',
            'user_id'    => $this->executive->id,
            'auth_token' => $this->token,
        ]);
    }

    private function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
    }

    /** The payload from the client's spec, posted verbatim. */
    private function payload()
    {
        return [
            'sample_type' => 'free',
            'customer_id' => null,
            'gst'         => 18,
            'items'       => [
                ['product_id' => $this->products[0]->id, 'pack_size_info' => '1 kg', 'pack_size' => '1', 'no_of_packs' => 1, 'qty' => 1, 'remarks' => 'Need only 250 gms'],
                ['product_id' => $this->products[1]->id, 'pack_size_info' => '1 kg', 'pack_size' => '1', 'no_of_packs' => 1, 'qty' => 1, 'remarks' => 'Need only 250 gms'],
                ['product_id' => $this->products[2]->id, 'pack_size_info' => '1 kg', 'pack_size' => '1', 'no_of_packs' => 1, 'qty' => 1, 'remarks' => 'Need only 250 gms'],
                ['product_id' => $this->products[3]->id, 'pack_size_info' => '1 kg', 'pack_size' => '1', 'no_of_packs' => 1, 'qty' => 1, 'remarks' => 'Need only 250 gms'],
            ],
            'purpose_reason_for_request' => 'Lab testing purposes for VSM Chemicals',
            'application_plan'           => '',
            'dispatch_dealer_id'         => null,
            'dispatch_customer_id'       => null,
            'dispatch_to'                => 'My Address',
            'dispatch_address'           => "Venkata Subramani\nROOM NO.228/2, 1ST FLOOR, COIMBATORE TAMILNADU 641004\nM. 9585539040",
            'is_specific_customer'       => 0,
            'product_known'              => 1,
            'target_competitor'          => 0,
            'competitor_category'        => null,
            'competitor_product_name'    => null,
            'competitor_make'            => null,
            'competitor_dealer_price'    => null,
            'competitor_customer_price'  => null,
            'competitor_dosage'          => null,
            'competitor_monthly_requirement_kg' => null,
            'competitor_specialty'       => null,
            'competitor_application_details' => null,
            'competitor_expectation'     => null,
        ];
    }

    public function testFourProductsBecomeFourRequestLines()
    {
        $response = $this->postJson('/api/executive/create-sampling-request', $this->payload(), $this->headers())
            ->assertStatus(200);

        $refs = $response->json('data.reference_nos');
        $this->assertCount(4, $refs);

        $groupNo = (int) explode('.', substr($refs[0], 3))[0];
        $this->assertSame([
            'SR-' . $groupNo . '.1',
            'SR-' . $groupNo . '.2',
            'SR-' . $groupNo . '.3',
            'SR-' . $groupNo . '.4',
        ], $refs);

        $lines = SamplingRequest::whereIn('reference_no', $refs)->get();

        $this->assertCount(4, $lines);
        foreach ($lines as $line) {
            $this->assertSame('pending', $line->status);
            $this->assertEquals($this->executive->id, $line->user_id);
            $this->assertSame('Lab testing purposes for VSM Chemicals', $line->purpose_reason_for_request,
                'the request-level block is copied onto every line');
            $this->assertEquals($this->executive->id, $line->dispatch_user_id,
                'no dealer and no customer means it goes to the requesting executive');
            $this->assertEquals(1, $line->requested_qty);
            $this->assertEquals(0, $line->total_dispatched_qty);
            $this->assertNull($line->approved_qty);
        }
    }

    public function testDispatchDealerIsCarriedThrough()
    {
        $dealerId = \App\Dealer::where('status', 1)->value('id');
        if (!$dealerId) {
            $this->markTestSkipped('Needs an active dealer.');
        }

        $payload = $this->payload();
        $payload['dispatch_dealer_id'] = $dealerId;

        $refs = $this->postJson('/api/executive/create-sampling-request', $payload, $this->headers())
            ->assertStatus(200)
            ->json('data.reference_nos');

        $line = SamplingRequest::where('reference_no', $refs[0])->firstOrFail();

        $this->assertEquals($dealerId, $line->dispatch_dealer_id);
        $this->assertNull($line->dispatch_user_id, 'the executive fallback only applies when no party is named');
    }

    public function testEveryLineIsLoggedAsCreated()
    {
        $refs = $this->postJson('/api/executive/create-sampling-request', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.reference_nos');

        $line = SamplingRequest::where('reference_no', $refs[0])->firstOrFail();

        $this->assertSame(1, $line->logs()->where('action', 'created')->count());
    }

    public function testValidationRejectsAnEmptyOrUnknownProduct()
    {
        $this->postJson('/api/executive/create-sampling-request', ['items' => []], $this->headers())
            ->assertStatus(422);

        $payload = $this->payload();
        $payload['items'][0]['product_id'] = 99999999;

        $this->postJson('/api/executive/create-sampling-request', $payload, $this->headers())
            ->assertStatus(422);

        $payload = $this->payload();
        $payload['items'][1]['qty'] = 0;

        $this->postJson('/api/executive/create-sampling-request', $payload, $this->headers())
            ->assertStatus(422);
    }

    public function testRequestsCanBeListedBack()
    {
        $refs = $this->postJson('/api/executive/create-sampling-request', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.reference_nos');

        $listed = $this->getJson('/api/executive/sampling-requests', $this->headers())
            ->assertStatus(200)
            ->json('data.sampling_requests');

        $listedRefs = array_column($listed, 'reference_no');
        foreach ($refs as $ref) {
            $this->assertContains($ref, $listedRefs);
        }

        $pendingOnly = $this->getJson('/api/executive/sampling-requests?status=closed', $this->headers())
            ->assertStatus(200)
            ->json('data.sampling_requests');

        $this->assertNotContains($refs[0], array_column($pendingOnly, 'reference_no'));
    }

    /**
     * The old endpoint still accepts the payload builds in the field already
     * send, but now writes the new tables so those requests reach the admin.
     */
    public function testLegacyEndpointWritesTheNewTables()
    {
        $beforeNew = SamplingRequest::count();
        $beforeOld = \App\Sampling::count();

        $response = $this->postJson('/api/executive/create-sample-request', [
            'sample_type'      => 'free',
            'required_through' => 'courier',
            'request_type'     => 'On Request',
            'remarks'          => 'Trial at the customer plant',
            'customer_id'      => null,
            'dispatch_to'      => 'My Address',
            'dispatch_address' => "Old App User\nSome Street",
            'items'            => [
                ['product_id' => $this->products[0]->id, 'qty' => 2, 'pack_size' => 1, 'no_of_packs' => 2, 'remarks' => 'first trial'],
                ['product_id' => $this->products[1]->id, 'qty' => 1, 'pack_size' => 1, 'no_of_packs' => 1],
            ],
        ], $this->headers())->assertStatus(200);

        $this->assertSame($beforeNew + 2, SamplingRequest::count(), 'one new request per product');
        $this->assertSame($beforeOld, \App\Sampling::count(), 'the legacy tables are no longer written');

        $refs  = $response->json('data.reference_nos');
        $lines = SamplingRequest::whereIn('reference_no', $refs)->get();

        $this->assertCount(2, $lines);
        foreach ($lines as $line) {
            $this->assertSame('pending', $line->status);
            $this->assertEquals($this->executive->id, $line->user_id);
            $this->assertEquals($this->executive->id, $line->dispatch_user_id,
                'an old build names no party, so it stays with the executive');
            // The old payload has no purpose field, so its remarks stand in.
            $this->assertSame('Trial at the customer plant', $line->purpose_reason_for_request);
        }
    }

    /** Both endpoints must produce the same rows from the same products. */
    public function testBothEndpointsStoreTheSameShape()
    {
        $legacyRefs = $this->postJson('/api/executive/create-sample-request', [
            'items' => [['product_id' => $this->products[0]->id, 'qty' => 3, 'pack_size' => 1, 'no_of_packs' => 3]],
        ], $this->headers())->assertStatus(200)->json('data.reference_nos');

        $newRefs = $this->postJson('/api/executive/create-sampling-request', [
            'items' => [['product_id' => $this->products[0]->id, 'qty' => 3, 'pack_size' => 1, 'no_of_packs' => 3]],
        ], $this->headers())->assertStatus(200)->json('data.reference_nos');

        $legacy = SamplingRequest::where('reference_no', $legacyRefs[0])->firstOrFail();
        $new    = SamplingRequest::where('reference_no', $newRefs[0])->firstOrFail();

        foreach (['status', 'user_id', 'product_id', 'requested_qty', 'pack_size', 'no_of_packs', 'dispatch_user_id'] as $field) {
            $this->assertEquals($new->$field, $legacy->$field, $field . ' differs between the two endpoints');
        }
    }

    /** The new model has no edit, so a legacy `id` is refused, not duplicated. */
    public function testLegacyEndpointRefusesAnEdit()
    {
        $before = SamplingRequest::count();

        $this->postJson('/api/executive/create-sample-request', [
            'id'    => 123,
            'items' => [['product_id' => $this->products[0]->id, 'qty' => 1]],
        ], $this->headers())->assertStatus(422);

        $this->assertSame($before, SamplingRequest::count(), 'nothing is created');
    }
}
