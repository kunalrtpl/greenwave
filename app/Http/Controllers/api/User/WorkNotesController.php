<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\WorkNote;
use App\WorkNoteAttachment;
use Carbon\Carbon;

class WorkNotesController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $token      = $request->header('Authorization');
            $resp       = AuthToken::verifyUser($token);
            $this->resp = $resp;
        }
    }

    // ─── 1. List Work Notes ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $data = $request->all();

        $month = isset($data['month']) && !empty($data['month'])
            ? (int) $data['month']
            : (int) Carbon::now()->format('m');

        $year = isset($data['year']) && !empty($data['year'])
            ? (int) $data['year']
            : (int) Carbon::now()->format('Y');

        $userId = isset($data['user_id']) && !empty($data['user_id'])
            ? (int) $data['user_id']
            : $resp['user']['id'];

        $workNotes = WorkNote::with(['attachments'])
            ->where('user_id', $userId)
            ->whereYear('request_date', $year)
            ->whereMonth('request_date', $month)
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

        $result = [
            'month'      => $month,
            'year'       => $year,
            'user_id'    => $userId,
            'work_notes' => $workNotes,
        ];

        $message = "Work notes fetched successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 2. Create Work Note ──────────────────────────────────────────────────
    /**
     * POST /api/work-notes
     * Content-Type: multipart/form-data
     *
     * Fields:
     *   request_date              string   YYYY-MM-DD   required
     *   type                      string                required
     *   type_other                string                required when type = 'other'
     *   title                     string                required
     *   note                      string                optional
     *
     *   attachments[]             file[]                optional, multiple
     *   attachment_mime_types[]   string[]              optional, index-matched
     *   attachment_durations[]    integer[]             optional, seconds (for voice notes)
     *
     * App determines voice note vs file using attachment_mime_types[]:
     *   audio/* mime  → saved as type = 'voice_note'
     *   anything else → saved as type = 'attachment'
     */
    public function store(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        // ── Validate ──────────────────────────────────────────────────────────
        $rules = [
            'request_date'              => 'required|date_format:Y-m-d',
            'type'                      => 'required|string|max:100',
            'type_other'                => 'nullable|string|max:255',
            'title'                     => 'required|string|max:255',
            'note'                      => 'nullable|string',

            'attachments'               => 'nullable|array',
            'attachments.*'             => 'file|max:51200',       // 50 MB max per file
            'attachment_mime_types'     => 'nullable|array',
            'attachment_mime_types.*'   => 'nullable|string|max:100',
            'attachment_durations'      => 'nullable|array',
            'attachment_durations.*'    => 'nullable|integer|min:0',
        ];

        if (strtolower(trim($request->input('type', ''))) === 'other') {
            $rules['type_other'] = 'required|string|max:255';
        }

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(apiErrorResponse($message), 422);
        }

        $userId = $resp['user']['id'];

        // ── Directories in public/ ────────────────────────────────────────────
        $voiceDir      = public_path('work_notes/voice');
        $attachmentDir = public_path('work_notes/attachments');

        if (!file_exists($voiceDir))      mkdir($voiceDir,      0775, true);
        if (!file_exists($attachmentDir)) mkdir($attachmentDir, 0775, true);

        // ── Create WorkNote ───────────────────────────────────────────────────
        $typeValue      = $request->input('type');
        $typeOtherValue = strtolower(trim($typeValue)) === 'other'
            ? $request->input('type_other')
            : null;

        $workNote = WorkNote::create([
            'user_id'      => $userId,
            'request_date' => $request->input('request_date'),
            'type'         => $typeValue,
            'type_other'   => $typeOtherValue,
            'title'        => $request->input('title'),
            'note'         => $request->input('note'),
        ]);

        // ── Save Attachments (voice notes auto-detected by mime type) ─────────
        if ($request->hasFile('attachments')) {
            $attachmentFiles     = $request->file('attachments');
            $attachmentMimeTypes = $request->input('attachment_mime_types', []);
            $attachmentDurations = $request->input('attachment_durations', []);

            foreach ($attachmentFiles as $index => $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $mimeType = isset($attachmentMimeTypes[$index]) && !empty($attachmentMimeTypes[$index])
                    ? $attachmentMimeTypes[$index]
                    : $file->getMimeType();

                // Auto-detect voice note from mime type (audio/*)
                $isVoiceNote = strpos(strtolower($mimeType), 'audio/') === 0;

                if ($isVoiceNote) {
                    $fileName = 'voice_' . time() . '_' . uniqid() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move($voiceDir, $fileName);
                } else {
                    $fileName = 'attach_' . time() . '_' . uniqid() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move($attachmentDir, $fileName);
                }

                WorkNoteAttachment::create([
                    'work_note_id'     => $workNote->id,
                    'type'             => $isVoiceNote ? 'voice_note' : 'attachment',
                    'file'             => $fileName,
                    'original_name'    => $file->getClientOriginalName(),
                    'mime_type'        => $mimeType,
                    'duration_seconds' => isset($attachmentDurations[$index]) ? (int) $attachmentDurations[$index] : 0,
                ]);
            }
        }

        $workNote->load(['attachments']);

        $result  = ['work_note' => $workNote];
        $message = "Work note created successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }
}