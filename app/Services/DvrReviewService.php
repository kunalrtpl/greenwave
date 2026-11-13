<?php

namespace App\Services;

use App\UserDvr;
use App\UserDvrQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The admin's review of a DVR: verifying it and the query thread on it.
 * Shared by the admin screen and the executive app so both sides follow the
 * same rules.
 *
 * Verification only moves forward. A DVR starts 'Pending Verification' and an
 * admin can mark it 'Verified'; nothing puts it back to pending.
 *
 * Queries can only be started by an admin. Each admin message sets the DVR's
 * query_status to 'Query Raised' and emails the employee; each employee answer
 * sets it to 'Query Responded' and emails every admin who has written on the
 * thread. The employee cannot write until an admin has.
 */
class DvrReviewService
{
    /**
     * Mark a DVR verified. Verifying an already-verified DVR is a no-op.
     *
     * @return UserDvr
     */
    public function verify(UserDvr $dvr, $adminId)
    {
        if ($dvr->isVerified()) {
            return $dvr;
        }

        $dvr->dvr_status      = UserDvr::STATUS_VERIFIED;
        $dvr->dvr_verified_by = $adminId;
        $dvr->dvr_verified_at = now();
        $dvr->save();

        return $dvr;
    }

    /**
     * Post a message on the DVR's thread, move query_status, and email the
     * other side.
     *
     * @param  string $senderType UserDvrQuery::SENDER_ADMIN|SENDER_EMPLOYEE
     * @throws ValidationException when an employee tries to start a thread
     */
    public function post(UserDvr $dvr, $senderType, $senderId, $message)
    {
        $fromAdmin = $senderType === UserDvrQuery::SENDER_ADMIN;

        if (!$fromAdmin && !$this->hasStarted($dvr->id)) {
            throw ValidationException::withMessages([
                'message' => 'There is no query on this DVR to respond to.',
            ]);
        }

        $query = DB::transaction(function () use ($dvr, $senderType, $senderId, $message, $fromAdmin) {
            $query = UserDvrQuery::create([
                'user_dvr_id' => $dvr->id,
                'sender_id'   => $senderId,
                'sender_type' => $senderType,
                'message'     => trim($message),
                // Whoever wrote it has obviously read it; the other side has not.
                'admin_read'  => $fromAdmin ? 1 : 0,
                'user_read'   => $fromAdmin ? 0 : 1,
            ]);

            $dvr->query_status = $fromAdmin ? UserDvr::QUERY_RAISED : UserDvr::QUERY_RESPONDED;
            $dvr->save();

            return $query;
        });

        // After the commit, so a failed email can never undo the message.
        app(DvrEmailService::class)->queryPosted($dvr, $query);

        return $query;
    }

    /** True once an admin has written on the thread — the employee may then answer. */
    public function hasStarted($dvrId)
    {
        return UserDvrQuery::where('user_dvr_id', $dvrId)
            ->where('sender_type', UserDvrQuery::SENDER_ADMIN)
            ->exists();
    }

    /**
     * The whole thread, marking everything the opening side had not seen as
     * read. Returns the messages plus both sides' counts.
     *
     * @param string $side 'admin' or 'employee' — who is opening it
     */
    public function thread(UserDvr $dvr, $side)
    {
        if ($side === UserDvrQuery::SENDER_ADMIN) {
            UserDvrQuery::where('user_dvr_id', $dvr->id)->unreadByAdmin()->update(['admin_read' => 1]);
        } else {
            UserDvrQuery::where('user_dvr_id', $dvr->id)->unreadByEmployee()->update(['user_read' => 1]);
        }

        $messages = UserDvrQuery::with('sender:id,name')
            ->where('user_dvr_id', $dvr->id)
            ->orderBy('id')
            ->get();

        $started = $messages->contains('sender_type', UserDvrQuery::SENDER_ADMIN);

        return [
            'queries'      => $messages->map(function ($q) { return $q->toThreadArray(); })->values(),
            'total'        => $messages->count(),
            'unread_admin' => $this->unreadForAdmin($dvr->id),
            'unread_user'  => $this->unreadForEmployee($dvr->id),
            'dvr_status'   => $dvr->dvr_status,
            'query_status' => $dvr->query_status,
            // The app may only answer an existing query; the admin can always write.
            'can_post'     => $side === UserDvrQuery::SENDER_ADMIN ? true : $started,
        ];
    }

    public function unreadForAdmin($dvrId)
    {
        return UserDvrQuery::where('user_dvr_id', $dvrId)->unreadByAdmin()->count();
    }

    public function unreadForEmployee($dvrId)
    {
        return UserDvrQuery::where('user_dvr_id', $dvrId)->unreadByEmployee()->count();
    }

    /**
     * Totals and unread counts for a page of DVRs, keyed by DVR id — one query
     * each instead of two per row.
     *
     * @param  string $side whose unread count: 'admin' or 'employee'
     * @return array ['total' => [id => n], 'unread' => [id => n]]
     */
    public function countsFor(array $dvrIds, $side = UserDvrQuery::SENDER_ADMIN)
    {
        if (!$dvrIds) {
            return ['total' => [], 'unread' => []];
        }

        $total = UserDvrQuery::selectRaw('user_dvr_id, COUNT(*) as n')
            ->whereIn('user_dvr_id', $dvrIds)
            ->groupBy('user_dvr_id')
            ->pluck('n', 'user_dvr_id')
            ->all();

        $unreadQuery = UserDvrQuery::selectRaw('user_dvr_id, COUNT(*) as n')->whereIn('user_dvr_id', $dvrIds);
        $side === UserDvrQuery::SENDER_ADMIN ? $unreadQuery->unreadByAdmin() : $unreadQuery->unreadByEmployee();

        $unread = $unreadQuery->groupBy('user_dvr_id')->pluck('n', 'user_dvr_id')->all();

        return ['total' => $total, 'unread' => $unread];
    }
}
