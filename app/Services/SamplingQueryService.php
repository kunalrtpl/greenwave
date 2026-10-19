<?php

namespace App\Services;

use App\SamplingRequest;
use App\SamplingRequestQuery;
use Illuminate\Validation\ValidationException;

/**
 * The query thread on a sample request, shared by the admin screen and the
 * executive app so both sides behave identically.
 *
 * Posting is only allowed while the request is pending or approved. Once it is
 * closed or declined the thread is readable history and nothing more.
 *
 * Each message notifies the other side by email: the employee writes and the
 * admin is told, the admin writes and the employee is told.
 */
class SamplingQueryService
{
    /**
     * Post a message and notify the other side.
     *
     * @param  string $senderType SamplingRequestQuery::SENDER_ADMIN|SENDER_EMPLOYEE
     * @throws ValidationException when the thread is closed
     */
    public function post(SamplingRequest $request, $senderType, $senderId, $message)
    {
        if (!$request->acceptsQueries()) {
            throw ValidationException::withMessages([
                'message' => $request->queriesClosedReason(),
            ]);
        }

        $fromAdmin = $senderType === SamplingRequestQuery::SENDER_ADMIN;

        $query = SamplingRequestQuery::create([
            'sampling_request_id' => $request->id,
            'sender_id'           => $senderId,
            'sender_type'         => $senderType,
            'message'             => trim($message),
            // Whoever wrote it has obviously read it; the other side has not.
            'admin_read'          => $fromAdmin ? 1 : 0,
            'user_read'           => $fromAdmin ? 0 : 1,
        ]);

        app(SamplingEmailService::class)->queryPosted($request, $query);

        return $query;
    }

    /**
     * The whole thread, marking everything the opening side had not seen as
     * read. Returns the messages plus both sides' counts.
     *
     * @param string $side 'admin' or 'employee' — who is opening it
     */
    public function thread(SamplingRequest $request, $side)
    {
        if ($side === SamplingRequestQuery::SENDER_ADMIN) {
            SamplingRequestQuery::where('sampling_request_id', $request->id)
                ->unreadByAdmin()
                ->update(['admin_read' => 1]);
        } else {
            SamplingRequestQuery::where('sampling_request_id', $request->id)
                ->unreadByEmployee()
                ->update(['user_read' => 1]);
        }

        $messages = SamplingRequestQuery::with('sender:id,name')
            ->where('sampling_request_id', $request->id)
            ->orderBy('id')
            ->get();

        return [
            'queries'       => $messages->map(function ($q) { return $q->toThreadArray(); })->values(),
            'total'         => $messages->count(),
            'unread_admin'  => $this->unreadForAdmin($request->id),
            'unread_user'   => $this->unreadForEmployee($request->id),
            'can_post'      => $request->acceptsQueries(),
            'closed_reason' => $request->queriesClosedReason(),
        ];
    }

    public function unreadForAdmin($requestId)
    {
        return SamplingRequestQuery::where('sampling_request_id', $requestId)->unreadByAdmin()->count();
    }

    public function unreadForEmployee($requestId)
    {
        return SamplingRequestQuery::where('sampling_request_id', $requestId)->unreadByEmployee()->count();
    }

    /**
     * Totals and admin-unread counts for a page of requests, keyed by request
     * id — one query each instead of two per row.
     *
     * @return array ['total' => [id => n], 'unread' => [id => n]]
     */
    public function countsFor(array $requestIds)
    {
        if (!$requestIds) {
            return ['total' => [], 'unread' => []];
        }

        $total = SamplingRequestQuery::selectRaw('sampling_request_id, COUNT(*) as n')
            ->whereIn('sampling_request_id', $requestIds)
            ->groupBy('sampling_request_id')
            ->pluck('n', 'sampling_request_id')
            ->all();

        $unread = SamplingRequestQuery::selectRaw('sampling_request_id, COUNT(*) as n')
            ->whereIn('sampling_request_id', $requestIds)
            ->unreadByAdmin()
            ->groupBy('sampling_request_id')
            ->pluck('n', 'sampling_request_id')
            ->all();

        return ['total' => $total, 'unread' => $unread];
    }
}
