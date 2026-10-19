<?php

namespace Tests\Feature;

use App\Mail\DynamicMail;
use App\Product;
use App\SamplingRequest;
use App\Services\SamplingDispatchService;
use App\Services\SamplingRequestService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Who gets told what, and how many times.
 *
 * The counts matter as much as the content: a request carrying four products
 * must produce ONE email, not four, while an approval is per product because
 * each product is decided on its own.
 */
class SamplingEmailTest extends TestCase
{
    use DatabaseTransactions;

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
        $this->executive = User::marketingEmployees()->whereNotNull('users.email')->orderBy('users.id')->firstOrFail();
        $this->products  = Product::where('status', 1)->take(4)->get();

        if ($this->products->count() < 4) {
            $this->markTestSkipped('Needs at least four active products.');
        }

        $this->actingAs($this->admin);

        // The admin templates are addressed from their own to_emails, which is
        // editable from the admin panel. Pin it here so these tests check the
        // sending logic rather than whatever the panel currently holds.
        \App\EmailTemplate::whereIn('event_key', [
            'sample_request_created_admin', 'sample_voluntary_created_admin',
        ])->update(['to_emails' => json_encode(['admin@example.com'])]);

        Mail::fake();
    }

    /** Count the mails sent for a given template event key. */
    private function sentFor($eventKey)
    {
        $count = 0;
        Mail::assertSent(DynamicMail::class, function ($mail) use ($eventKey, &$count) {
            if ($mail->template->event_key === $eventKey) {
                $count++;
            }
            return true;
        });
        return $count;
    }

    private function raiseRequest($productCount = 4)
    {
        $items = [];
        for ($i = 0; $i < $productCount; $i++) {
            $items[] = ['product_id' => $this->products[$i]->id, 'pack_size' => 1, 'no_of_packs' => 2, 'qty' => 2];
        }

        return app(SamplingRequestService::class)->createFromApi([
            'sample_type' => 'free',
            'items'       => $items,
            'purpose_reason_for_request' => 'Lab testing',
            'dispatch_address'           => "Test\nSomewhere",
        ], $this->executive->id);
    }

    /** Four products in one post is one email to the employee and one to the admin. */
    public function testOneRequestSendsOneEmailEachHoweverManyProducts()
    {
        $lines = $this->raiseRequest(4);
        $this->assertCount(4, $lines);

        $this->assertSame(1, $this->sentFor('sample_request_created_employee'),
            'the employee gets one email for the whole request, not one per product');
        $this->assertSame(1, $this->sentFor('sample_request_created_admin'),
            'the admin gets one email for the whole request');
    }

    /** With no redirect configured, the employee's copy goes to the employee. */
    public function testRequestEmailGoesToTheEmployeeWhenNotRedirected()
    {
        config(['mail.redirect_to' => null]);

        $this->raiseRequest(2);

        $employee = $this->executive;

        Mail::assertSent(DynamicMail::class, function ($mail) use ($employee) {
            return $mail->template->event_key === 'sample_request_created_employee'
                && $mail->hasTo($employee->email);
        });
    }

    /**
     * With MAIL_REDIRECT_TO set, every message lands in that one inbox instead
     * — which is how this is tested without writing to real employees.
     */
    public function testRedirectSendsEverythingToTheTestInbox()
    {
        $inbox = 'redirect-target@example.com';
        config(['mail.redirect_to' => $inbox]);

        $this->raiseRequest(2);

        Mail::assertSent(DynamicMail::class, function ($mail) use ($inbox) {
            return $mail->hasTo($inbox);
        });

        $employee = $this->executive;
        Mail::assertNotSent(DynamicMail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->email);
        });
    }

    /** Approving is per product, because each product is decided on its own. */
    public function testApprovalSendsOneEmailPerProduct()
    {
        $lines = $this->raiseRequest(3);
        $service = app(SamplingRequestService::class);

        $service->approve($lines[0]->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 2]);
        $service->approve($lines[1]->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 2]);

        $this->assertSame(2, $this->sentFor('sample_request_decided_employee'));
    }

    public function testDecliningAlsoNotifiesTheEmployee()
    {
        $lines = $this->raiseRequest(1);

        app(SamplingRequestService::class)->decline($lines[0]->fresh(), ['decline_reason' => 'Not needed']);

        $this->assertSame(1, $this->sentFor('sample_request_decided_employee'));
    }

    /** Several requests on one challan is one email, not one per request. */
    public function testDispatchSendsOneEmailPerEmployeePerChallan()
    {
        $lines   = $this->raiseRequest(2);
        $service = app(SamplingRequestService::class);

        foreach ($lines as $line) {
            $service->approve($line->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 2]);
            $service->markReady($line->fresh(), []);
        }

        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user',
            'dispatch_type'   => 'request',
            'user_id'         => $this->executive->id,
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-T1',
            'transport'       => 'Courier',
            'lr_no'           => 'DKT-1',
            'items'           => [
                ['sampling_request_id' => $lines[0]->id, 'dispatch_qty' => 2,
                 'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id],
                ['sampling_request_id' => $lines[1]->id, 'dispatch_qty' => 2,
                 'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id],
            ],
        ]);

        $this->assertSame(1, $this->sentFor('sample_request_dispatched_employee'),
            'two requests on one challan is one email');
    }

    /** A voluntary sample has no requester, so the admin is told instead. */
    public function testVoluntarySampleNotifiesTheAdmin()
    {
        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'user',
            'dispatch_type'   => 'voluntary',
            'user_id'         => $this->executive->id,
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-V1',
            'transport'       => 'Transport',
            'lr_no'           => 'LR-1',
            'voluntary_items' => [
                ['product_id' => $this->products[0]->id, 'approved_pack_size' => 1, 'approved_no_of_packs' => 2,
                 'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id],
                ['product_id' => $this->products[1]->id, 'approved_pack_size' => 1, 'approved_no_of_packs' => 1,
                 'dispatch_party_type' => 'user', 'dispatch_user_id' => $this->executive->id],
            ],
        ]);

        $this->assertSame(1, $this->sentFor('sample_voluntary_created_admin'),
            'two voluntary products on one dispatch is one admin email');
        $this->assertSame(0, $this->sentFor('sample_request_created_employee'),
            'nobody requested it, so there is no request-raised email');
    }

    /** A dealer sample belongs to no employee, so only the admin hears about it. */
    public function testDealerVoluntarySampleOnlyNotifiesTheAdmin()
    {
        $dealer = \App\Dealer::where('status', 1)->whereNull('parent_id')->first();
        if (!$dealer) {
            $this->markTestSkipped('Needs an active head-office dealer.');
        }

        app(SamplingDispatchService::class)->dispatch([
            'answerable_type' => 'dealer',
            'dealer_id'       => $dealer->id,
            'dispatch_type'   => 'voluntary',
            'dispatch_date'   => date('Y-m-d'),
            'invoice_no'      => 'CH-D1',
            'transport'       => 'Courier',
            'lr_no'           => 'DKT-D1',
            'voluntary_items' => [
                ['product_id' => $this->products[0]->id, 'approved_pack_size' => 1, 'approved_no_of_packs' => 2,
                 'dispatch_party_type' => 'dealer', 'dispatch_dealer_id' => $dealer->id],
            ],
        ]);

        $this->assertSame(1, $this->sentFor('sample_voluntary_created_admin'));
        $this->assertSame(0, $this->sentFor('sample_request_dispatched_employee'),
            'a dealer sample has no employee to tell');
    }

    /* ─────────────────────────── What is in them ─────────────────────────── */

    /**
     * Render a sent mail's body so the content can be asserted on.
     *
     * Mail::fake never builds the view, and Mailable::render() goes back
     * through the faked mailer, so the template's own view is rendered with
     * the data the mail was given — which is exactly what the mailer does.
     *
     * @return string the HTML the recipient would see
     */
    private function renderedBody($eventKey)
    {
        $html = null;

        Mail::assertSent(DynamicMail::class, function ($mail) use ($eventKey, &$html) {
            if ($html === null && $mail->template->event_key === $eventKey) {
                $html = view($mail->template->blade_view, $mail->mailData)->render();
            }
            return true;
        });

        $this->assertNotNull($html, 'no ' . $eventKey . ' mail was sent');

        return $html;
    }

    /** The admin's copy carries a button straight to the pending list. */
    public function testTheAdminEmailLinksToThePendingList()
    {
        config(['mail.admin_panel_url' => 'http://localhost:8000']);

        $this->raiseRequest(2);

        $html = $this->renderedBody('sample_request_created_admin');

        $this->assertContains('http://localhost:8000/admin/sampling-requests/pending', $html,
            'the admin can click through to the screen the decision is made on');
        $this->assertContains('Open Pending Sample Requests', $html);
    }

    /** The link follows the configured panel URL, not the app's own host. */
    public function testThePendingLinkFollowsTheConfiguredPanelUrl()
    {
        config(['mail.admin_panel_url' => 'https://g2app.in']);

        $this->raiseRequest(1);

        $this->assertContains('https://g2app.in/admin/sampling-requests/pending',
            $this->renderedBody('sample_request_created_admin'));
    }

    /**
     * A query email repeats what the employee asked for — the product, the
     * packing, the qty and why — so neither side has to open the panel.
     */
    public function testTheQueryEmailCarriesTheWholeRequest()
    {
        $line = app(SamplingRequestService::class)->createFromApi([
            'sample_type' => 'free',
            'items'       => [[
                'product_id'  => $this->products[0]->id,
                'pack_size'   => 1,
                'no_of_packs' => 3,
                'qty'         => 3,
                'remarks'     => 'Only 250 gms is enough',
            ]],
            'purpose_reason_for_request' => 'Trial at the customer plant',
            'application_plan'           => 'Two machines for a week',
        ], $this->executive->id)->first();

        app(\App\Services\SamplingQueryService::class)->post(
            $line->fresh(), \App\SamplingRequestQuery::SENDER_EMPLOYEE,
            $this->executive->id, 'Can I get 5 Kg instead?'
        );

        $html = $this->renderedBody('sample_query_from_employee_admin');

        $this->assertContains('Can I get 5 Kg instead?', $html, 'the message itself');
        $this->assertContains($line->reference_no, $html);
        $this->assertContains($this->products[0]->product_name, $html, 'the product asked for');
        $this->assertContains('3 &times; 1 Kg', $html, 'the packing asked for');
        $this->assertContains('3 Kg', $html, 'the qty asked for');
        $this->assertContains('Only 250 gms is enough', $html, 'the item remarks');
        $this->assertContains('Trial at the customer plant', $html, 'the purpose');
        $this->assertContains('Two machines for a week', $html, 'the application plan');
    }

    /** Once approved, the query email shows the approved figures as well. */
    public function testTheQueryEmailShowsWhatWasApproved()
    {
        $line    = $this->raiseRequest(1)->first();
        $service = app(SamplingRequestService::class);

        $service->approve($line->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 1]);

        app(\App\Services\SamplingQueryService::class)->post(
            $line->fresh(), \App\SamplingRequestQuery::SENDER_ADMIN,
            $this->admin->id, 'Only 1 Kg can be spared.'
        );

        $html = $this->renderedBody('sample_query_from_admin_employee');

        $this->assertContains('Approved', $html);
        $this->assertContains('1 &times; 1 Kg', $html, 'the approved packing');
        $this->assertContains('Dispatched', $html, 'and how much has gone out so far');
    }

    /**
     * Declining after approving tells the employee the approval is withdrawn,
     * not just that the request was refused — they were already told it was
     * coming.
     */
    public function testDecliningAnApprovedRequestSaysTheApprovalIsWithdrawn()
    {
        $line    = $this->raiseRequest(1)->first();
        $service = app(SamplingRequestService::class);

        $service->approve($line->fresh(), ['approved_pack_size' => 1, 'approved_no_of_packs' => 2]);
        $service->decline($line->fresh(), ['decline_reason' => 'Stock needed elsewhere']);

        $this->assertSame(2, $this->sentFor('sample_request_decided_employee'),
            'one email for the approval, one for the decline');

        // The last decided email is the decline.
        $html = null;
        Mail::assertSent(DynamicMail::class, function ($mail) use (&$html) {
            if ($mail->template->event_key === 'sample_request_decided_employee'
                && $mail->mailData['request']->status === 'declined') {
                $html = view($mail->template->blade_view, $mail->mailData)->render();
            }
            return true;
        });

        $this->assertNotNull($html);
        $this->assertContains('has been', $html);
        $this->assertContains('withdrawn', $html);
        $this->assertContains('Previously Approved', $html);
        $this->assertContains('Stock needed elsewhere', $html);
    }

    /** A failing mailer must never roll back the thing that triggered it. */
    public function testEmailFailureDoesNotBreakTheFlow()
    {
        \App\EmailTemplate::whereIn('event_key', [
            'sample_request_created_employee', 'sample_request_created_admin',
        ])->update(['blade_view' => 'emails.sampling.does_not_exist']);

        $lines = $this->raiseRequest(2);

        $this->assertCount(2, $lines, 'the request is still created');
        $this->assertSame(2, SamplingRequest::whereIn('id', $lines->pluck('id'))->count());
    }
}
