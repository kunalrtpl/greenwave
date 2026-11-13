<?php

namespace App\Services;

use App\User;
use App\UserDvr;
use App\UserDvrQuery;

/**
 * Emails sent from the DVR review.
 *
 *   admin writes     the employee who filed the DVR is told a query was raised.
 *   employee answers every admin who has written on that DVR's thread is told,
 *                    so when several admins are involved, all of them hear it.
 *
 * EmailService swallows and logs its own failures, and DvrReviewService calls
 * this after its transaction has committed, so a bounced email never undoes a
 * message. While config('mail.redirect_to') is set, every message goes to that
 * one inbox instead — see EmailService::applyRedirect().
 */
class DvrEmailService
{
    const EVENT_QUERY_TO_EMPLOYEE = 'dvr_query_from_admin_employee';
    const EVENT_QUERY_TO_ADMIN    = 'dvr_query_from_employee_admin';

    public function queryPosted(UserDvr $dvr, UserDvrQuery $query)
    {
        $dvr = $dvr->fresh(['user', 'customer', 'customer_register_request']);
        $query->loadMissing('sender:id,name');

        $data = [
            'dvr'         => $dvr,
            'query'       => $query,
            'dvrRef'      => $this->reference($dvr),
            'customer'    => $this->customerName($dvr),
            'threadCount' => UserDvrQuery::where('user_dvr_id', $dvr->id)->count(),
            'thread'      => UserDvrQuery::with('sender:id,name')->where('user_dvr_id', $dvr->id)->orderBy('id')->get(),
            'panelUrl'    => config('mail.admin_panel_url') . '/admin/dvrs/' . $dvr->id,
        ];

        if ($query->sender_type === UserDvrQuery::SENDER_ADMIN) {
            $employee = $dvr->user;
            if ($employee && $employee->email) {
                EmailService::send(self::EVENT_QUERY_TO_EMPLOYEE, $data + ['audience' => 'employee'], $employee->email);
            }
            return;
        }

        // The employee answered: every admin who has taken part in this thread.
        $adminEmails = User::whereIn('id', UserDvrQuery::where('user_dvr_id', $dvr->id)
                ->where('sender_type', UserDvrQuery::SENDER_ADMIN)
                ->whereNotNull('sender_id')
                ->distinct()
                ->pluck('sender_id'))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->unique()
            ->values()
            ->all();

        if ($adminEmails) {
            EmailService::send(self::EVENT_QUERY_TO_ADMIN, $data + ['audience' => 'admin'], $adminEmails);
        }
    }

    /** "CVR #1155 · KPR Mills · 11 Nov 2026" — used in the subject line. The client calls a DVR a CVR in emails. */
    private function reference(UserDvr $dvr)
    {
        $parts = ['CVR #' . $dvr->id];
        if ($name = $this->customerName($dvr)) {
            $parts[] = $name;
        }
        if ($dvr->dvr_date) {
            $parts[] = \Carbon\Carbon::parse($dvr->dvr_date)->format('d M Y');
        }
        return implode(' · ', $parts);
    }

    private function customerName(UserDvr $dvr)
    {
        return optional($dvr->customer)->name ?: optional($dvr->customer_register_request)->name;
    }
}
