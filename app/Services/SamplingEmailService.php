<?php

namespace App\Services;

use App\SamplingRequest;
use App\User;
use Illuminate\Support\Collection;

/**
 * Every email the sampling module sends.
 *
 * Who hears about what:
 *
 *   raised      the employee who raised it, and the admin — ONE email each per
 *               request, so four products in one post is one email, not four.
 *   decided     the employee, one email per product, because each product is
 *               approved or declined on its own.
 *   dispatched  the employee the material is answerable to, one email per
 *               dispatch, listing every one of their requests on that challan.
 *   voluntary   the admin, when a sample is raised by the admin at dispatch
 *               time. Nothing was requested from the field, so this email is
 *               the only record of it; one per dispatch, never per product.
 *   query       the other side of the thread — the employee writes and the
 *               admin hears, the admin writes and the employee hears.
 *
 * Nothing here is allowed to break the thing that triggered it: EmailService
 * swallows and logs its own failures, and the callers invoke these methods
 * after their database transaction has committed, so a bounced email can never
 * roll back an approval or a dispatch.
 *
 * While config('mail.redirect_to') is set, every message goes to that one inbox
 * instead of to real employees — see EmailService::applyRedirect().
 */
class SamplingEmailService
{
    const EVENT_CREATED_EMPLOYEE    = 'sample_request_created_employee';
    const EVENT_CREATED_ADMIN       = 'sample_request_created_admin';
    const EVENT_DECIDED_EMPLOYEE    = 'sample_request_decided_employee';
    const EVENT_DISPATCHED_EMPLOYEE = 'sample_request_dispatched_employee';
    const EVENT_VOLUNTARY_ADMIN     = 'sample_voluntary_created_admin';
    const EVENT_QUERY_TO_ADMIN      = 'sample_query_from_employee_admin';
    const EVENT_QUERY_TO_EMPLOYEE   = 'sample_query_from_admin_employee';

    /**
     * A request has come in from the app.
     *
     * @param Collection $requests the lines created by one post, all of one request
     */
    public function requestRaised(Collection $requests)
    {
        if ($requests->isEmpty()) {
            return;
        }

        $head     = $requests->first()->fresh(['user', 'customer', 'product']);
        $requests = $this->withRelations($requests);
        $employee = $head->user;

        $data = [
            'requests'       => $requests,
            'head'           => $head,
            'employee'       => $employee,
            'groupReference' => $head->group_reference,
            // The admin's copy carries a button straight to the screen the
            // decision is made on.
            'pendingUrl'     => $this->panelUrl('admin/sampling-requests/pending'),
        ];

        // The employee who raised it.
        if ($employee && $employee->email) {
            EmailService::send(self::EVENT_CREATED_EMPLOYEE, $data, $employee->email);
        }

        // The admin — recipients come from the template's own to_emails.
        EmailService::send(self::EVENT_CREATED_ADMIN, $data);
    }

    /**
     * One product has been approved or declined. The template decides which of
     * the two it is from the request's status, so both share one event.
     */
    public function requestDecided(SamplingRequest $request)
    {
        $request  = $request->fresh(['user', 'customer', 'product', 'requested_product']);
        $employee = $request->user;

        if (!$employee || !$employee->email) {
            return;
        }

        EmailService::send(self::EVENT_DECIDED_EMPLOYEE, [
            'request'  => $request,
            'employee' => $employee,
        ], $employee->email);
    }

    /**
     * Material has gone out. One challan can carry several requests, so the
     * lines are grouped by the employee they belong to and each gets a single
     * email covering all of theirs.
     *
     * @param array $lines    [['request' => SamplingRequest, 'qty' => float, 'batch_no' => ?string], …]
     * @param array $dispatch ref_no, date, invoice_no, transport, lr_no, address
     */
    public function dispatched(array $lines, array $dispatch)
    {
        $byEmployee = [];

        foreach ($lines as $line) {
            $request = $line['request']->fresh(['user', 'product']);

            // A dealer's voluntary sample has no employee to tell.
            if (!$request->user_id) {
                continue;
            }

            $byEmployee[$request->user_id][] = [
                'request'     => $request,
                'product'     => $request->product,
                'batch_no'    => isset($line['batch_no']) ? $line['batch_no'] : null,
                'dispatch_qty' => $line['qty'],
                'pending_qty' => $request->pending_qty,
            ];
        }

        $this->voluntaryRaised($lines, $dispatch);

        foreach ($byEmployee as $userId => $employeeLines) {
            $employee = User::find($userId);
            if (!$employee || !$employee->email) {
                continue;
            }

            EmailService::send(self::EVENT_DISPATCHED_EMPLOYEE, [
                'lines'    => collect($employeeLines),
                'employee' => $employee,
                'dispatch' => $dispatch,
            ], $employee->email);
        }
    }

    /**
     * Someone has written on a request's query thread, so the other side is
     * told. One template serves both directions; `audience` tells the view
     * which way round it is being read.
     */
    public function queryPosted(SamplingRequest $request, \App\SamplingRequestQuery $query)
    {
        // The query email repeats everything the employee asked for, so both
        // sides can answer without opening the panel: the requested product
        // and packing as well as whatever has been approved against it.
        $request = $request->fresh(['user', 'product', 'requested_product', 'customer',
                                    'dispatch_dealer', 'dispatch_customer', 'dispatch_user']);
        $query->loadMissing('sender:id,name');

        $data = [
            'request'     => $request,
            'query'       => $query,
            'threadCount' => \App\SamplingRequestQuery::where('sampling_request_id', $request->id)->count(),
        ];

        if ($query->sender_type === \App\SamplingRequestQuery::SENDER_EMPLOYEE) {
            // Recipients come from the template's own to_emails.
            EmailService::send(self::EVENT_QUERY_TO_ADMIN, $data + ['audience' => 'admin']);
            return;
        }

        $employee = $request->user;
        if ($employee && $employee->email) {
            EmailService::send(self::EVENT_QUERY_TO_EMPLOYEE, $data + ['audience' => 'employee'], $employee->email);
        }
    }

    /**
     * A voluntary sample has no requester, so the admin is told instead. One
     * email per dispatch covering every voluntary product on it.
     */
    private function voluntaryRaised(array $lines, array $dispatch)
    {
        $voluntary = [];

        foreach ($lines as $line) {
            $request = $line['request']->fresh(['user', 'dealer', 'product']);
            if (!$request->is_voluntarily) {
                continue;
            }

            $voluntary[] = [
                'request'      => $request,
                'product'      => $request->product,
                'batch_no'     => isset($line['batch_no']) ? $line['batch_no'] : null,
                'dispatch_qty' => $line['qty'],
            ];
        }

        if (!$voluntary) {
            return;
        }

        $first = $voluntary[0]['request'];

        EmailService::send(self::EVENT_VOLUNTARY_ADMIN, [
            'lines'           => collect($voluntary),
            'dispatch'        => $dispatch,
            'accountable'     => $first->ownerName() ?: '—',
            'accountableType' => $first->ownerType(),
            'raisedBy'        => \Auth::check() ? \Auth::user()->name : null,
        ]);
    }

    /**
     * A link into the admin panel for an email to carry.
     *
     * config('mail.admin_panel_url') is its own setting because APP_URL is the
     * app's own host, which on a dev machine is not a host the person reading
     * the email can open.
     */
    private function panelUrl($path)
    {
        return config('mail.admin_panel_url') . '/' . ltrim($path, '/');
    }

    /** The views read product and customer names, so load them once up front. */
    private function withRelations(Collection $requests)
    {
        return SamplingRequest::with(['product:id,product_name,product_code', 'customer:id,name'])
            ->whereIn('id', $requests->pluck('id'))
            ->orderBy('line_no')
            ->get();
    }
}
