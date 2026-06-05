<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthToken;
use App\WorkNote;
use App\WorkNoteAttachment;
use App\UserDvr;
use App\UserScheduler;
use Carbon\Carbon;

class DailyActivityController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/user/daily-activity
    //
    // Query params (all optional):
    //   date    string  YYYY-MM-DD  (defaults to today)
    //   user_id integer             (defaults to logged-in user)
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status'] || !isset($resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized. Please try again after sometime.'), 422);
        }

        // ── Resolve date (default = today) ────────────────────────────────────
        $date = $request->filled('date')
            ? $request->input('date')
            : Carbon::today()->toDateString();

        // Basic format guard
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(apiErrorResponse('Invalid date format. Use YYYY-MM-DD.'), 422);
        }

        // ── Resolve user ──────────────────────────────────────────────────────
        $userId = $request->filled('user_id')
            ? (int) $request->input('user_id')
            : (int) $resp['user']['id'];

        // ── 1. DVRs ───────────────────────────────────────────────────────────
        $dvrs = UserDvr::with([
                'customer',
                'customer_register_request',
                'products',
                'trials.products',
                'trials.attachments',
                'trials.complaint_info',
                'trials.other_team_member_info',
                'customerContacts',
                'attachments',
                'complaint_sample',
                'market_sample',
                'sample_submission',
                'user_scheduler',
                'customer_contact_info',
                'query_info',
            ])
            ->where('user_id', $userId)
            ->whereDate('dvr_date', $date)
            ->orderBy('dvr_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        // ── 2. Work Notes ─────────────────────────────────────────────────────
        $workNotes = WorkNote::with(['attachments'])
            ->where('user_id', $userId)
            ->whereDate('request_date', $date)
            ->orderBy('request_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        // Append full URL to each attachment
        $workNotes->each(function ($workNote) {
            $workNote->attachments->each(function ($attachment) {
                $folder = $attachment->type === 'voice_note' ? 'voice' : 'attachments';
                $attachment->file_url = url('work_notes/' . $folder . '/' . $attachment->file);
            });
        });

        // ── 3. Schedulers ─────────────────────────────────────────────────────
        $schedulers = UserScheduler::with([
                'customer',
                'customer_register_request',
                'previous_scheduler',
                'next_scheduler',
            ])
            ->where('user_id', $userId)
            ->where('scheduler_date', $date)
            ->orderBy('scheduler_time', 'ASC')
            ->get();

        // ── Build result ──────────────────────────────────────────────────────
        $result = [
            'date'       => $date,
            'user_id'    => $userId,
            'dvrs'       => $dvrs,
            'work_notes' => $workNotes,
            'schedulers' => $schedulers,
        ];

        return response()->json(
            apiSuccessResponse('Daily activity data fetched successfully.', $result),
            200
        );
    }
}