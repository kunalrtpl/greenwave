<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    /**
     * GET /api/work-notes
     *
     * Query params:
     *   month        integer   optional, defaults to current month
     *   year         integer   optional, defaults to current year
     *   user_id      integer   optional, defaults to authenticated user
     *   related_to   string    optional  — filter by 'dealer' | 'customer' | 'customer_register_request'
     *   dealer_id    integer   optional  — filter by specific dealer
     *   customer_id  integer   optional  — filter by specific customer
     *   customer_register_request_id  integer  optional
     */
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

        $query = WorkNote::with(['attachments'])
            ->where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        // ── Optional filters ──────────────────────────────────────────────────
        if (!empty($data['related_to'])) {
            $query->where('related_to', $data['related_to']);
        }

        if (!empty($data['dealer_id'])) {
            $query->where('dealer_id', (int) $data['dealer_id']);
        }

        if (!empty($data['customer_id'])) {
            $query->where('customer_id', (int) $data['customer_id']);
        }

        if (!empty($data['customer_register_request_id'])) {
            $query->where('customer_register_request_id', (int) $data['customer_register_request_id']);
        }

        $workNotes = $query
            ->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        // ── Append full URL to each attachment ────────────────────────────────
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
     * Content-Type: multipart/form-data  (or application/json if no files)
     *
     * Fields:
     *   date                          string   YYYY-MM-DD   required
     *   related_to                    string                required  — dealer | customer | customer_register_request
     *   dealer_id                     integer               required when related_to = 'dealer'
     *   customer_id                   integer               required when related_to = 'customer'
     *   customer_register_request_id  integer               required when related_to = 'customer_register_request'
     *   subject                       string                optional
     *   activity_mode                 string                optional  — visit | call | email | …
     *   description                   string                optional
     *   key_take_away                 string                optional
     *   further_action_required       integer               optional  — 0 or 1 (default 0)
     *   action_date                   string   YYYY-MM-DD   optional  — required when further_action_required = 1
     *   action_time                   string   HH:MM        optional  — required when further_action_required = 1
     *   action_remarks                string                optional
     *
     *   attachments[]                 file[]                optional, multiple
     *   attachment_mime_types[]       string[]              optional, index-matched
     *   attachment_durations[]        integer[]             optional, seconds (for voice notes)
     */
    public function store(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        // ── Base validation rules ─────────────────────────────────────────────
        $rules = [
            'date'                         => 'required|date_format:Y-m-d',
            'related_to'                   => 'required|in:dealer,customer,customer_register_request',
            'dealer_id'                    => 'nullable|integer|exists:dealers,id',
            'customer_id'                  => 'nullable|integer|exists:customers,id',
            'customer_register_request_id' => 'nullable|integer|exists:customer_register_requests,id',
            'subject'                      => 'nullable|string|max:255',
            'activity_mode'                => 'nullable|string|max:100',
            'description'                  => 'nullable|string',
            'key_take_away'                => 'nullable|string',
            'further_action_required'      => 'nullable|in:0,1',
            'action_date'                  => 'nullable|date_format:Y-m-d',
            'action_time'                  => 'nullable|date_format:H:i',
            'action_remarks'               => 'nullable|string',

            'attachments'                  => 'nullable|array',
            'attachments.*'                => 'file|max:51200',       // 50 MB per file
            'attachment_mime_types'        => 'nullable|array',
            'attachment_mime_types.*'      => 'nullable|string|max:100',
            'attachment_durations'         => 'nullable|array',
            'attachment_durations.*'       => 'nullable|integer|min:0',
        ];

        // ── Conditional FK requirement based on related_to ────────────────────
        $relatedTo = strtolower(trim($request->input('related_to', '')));

        if ($relatedTo === 'dealer') {
            $rules['dealer_id'] = 'required|integer|exists:dealers,id';
        } elseif ($relatedTo === 'customer') {
            $rules['customer_id'] = 'required|integer|exists:customers,id';
        } elseif ($relatedTo === 'customer_register_request') {
            $rules['customer_register_request_id'] = 'required|integer|exists:customer_register_requests,id';
        }

        // ── Conditional action fields when further_action_required = 1 ────────
        if ((int) $request->input('further_action_required', 0) === 1) {
            $rules['action_date'] = 'required|date_format:Y-m-d';
            $rules['action_time'] = 'required|date_format:H:i';
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

        // ── Resolve nullable FK columns ───────────────────────────────────────
        $dealerId                  = null;
        $customerId                = null;
        $customerRegisterRequestId = null;

        if ($relatedTo === 'dealer') {
            $dealerId = (int) $request->input('dealer_id');
        } elseif ($relatedTo === 'customer') {
            $customerId = (int) $request->input('customer_id');
        } elseif ($relatedTo === 'customer_register_request') {
            $customerRegisterRequestId = (int) $request->input('customer_register_request_id');
        }

        $furtherActionRequired = (int) $request->input('further_action_required', 0);

        // ── Create WorkNote ───────────────────────────────────────────────────
        $workNote = WorkNote::create([
            'user_id'                      => $userId,
            'date'                         => $request->input('date'),
            'related_to'                   => $relatedTo,
            'dealer_id'                    => $dealerId,
            'customer_id'                  => $customerId,
            'customer_register_request_id' => $customerRegisterRequestId,
            'subject'                      => $request->input('subject'),
            'activity_mode'                => $request->input('activity_mode'),
            'description'                  => $request->input('description'),
            'key_take_away'                => $request->input('key_take_away'),
            'further_action_required'      => $furtherActionRequired,
            'action_date'                  => $furtherActionRequired ? $request->input('action_date') : null,
            'action_time'                  => $furtherActionRequired ? $request->input('action_time') : null,
            'action_remarks'               => $request->input('action_remarks'),
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

    // ─── 3. Show Single Work Note ─────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $userId = $resp['user']['id'];

        $workNote = WorkNote::with(['attachments'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$workNote) {
            $message = "Work note not found.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $workNote->attachments->each(function ($attachment) {
            $folder = $attachment->type === 'voice_note' ? 'voice' : 'attachments';
            $attachment->file_url = url('work_notes/' . $folder . '/' . $attachment->file);
        });

        $result  = ['work_note' => $workNote];
        $message = "Work note fetched successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 4. Update Work Note ──────────────────────────────────────────────────
    /**
     * POST /api/work-notes/{id}   (use POST with _method=PUT for multipart)
     * or PUT /api/work-notes/{id} (for JSON body without files)
     *
     * Same fields as store(); all optional except those conditionally required.
     * Attachments: newly uploaded files are APPENDED (existing ones are kept).
     */
    public function update(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $userId = $resp['user']['id'];

        $workNote = WorkNote::with(['attachments'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$workNote) {
            $message = "Work note not found.";
            return response()->json(apiErrorResponse($message), 422);
        }

        // ── Validation ────────────────────────────────────────────────────────
        $rules = [
            'date'                         => 'sometimes|date_format:Y-m-d',
            'related_to'                   => 'sometimes|in:dealer,customer,customer_register_request',
            'dealer_id'                    => 'nullable|integer|exists:dealers,id',
            'customer_id'                  => 'nullable|integer|exists:customers,id',
            'customer_register_request_id' => 'nullable|integer|exists:customer_register_requests,id',
            'subject'                      => 'nullable|string|max:255',
            'activity_mode'                => 'nullable|string|max:100',
            'description'                  => 'nullable|string',
            'key_take_away'                => 'nullable|string',
            'further_action_required'      => 'nullable|in:0,1',
            'action_date'                  => 'nullable|date_format:Y-m-d',
            'action_time'                  => 'nullable|date_format:H:i',
            'action_remarks'               => 'nullable|string',

            'attachments'                  => 'nullable|array',
            'attachments.*'                => 'file|max:51200',
            'attachment_mime_types'        => 'nullable|array',
            'attachment_mime_types.*'      => 'nullable|string|max:100',
            'attachment_durations'         => 'nullable|array',
            'attachment_durations.*'       => 'nullable|integer|min:0',
        ];

        // Resolve the effective related_to (incoming or existing)
        $relatedTo = strtolower(trim($request->input('related_to', $workNote->related_to ?? '')));

        if ($request->has('related_to')) {
            if ($relatedTo === 'dealer') {
                $rules['dealer_id'] = 'required|integer|exists:dealers,id';
            } elseif ($relatedTo === 'customer') {
                $rules['customer_id'] = 'required|integer|exists:customers,id';
            } elseif ($relatedTo === 'customer_register_request') {
                $rules['customer_register_request_id'] = 'required|integer|exists:customer_register_requests,id';
            }
        }

        $furtherActionRequired = $request->has('further_action_required')
            ? (int) $request->input('further_action_required')
            : $workNote->further_action_required;

        if ($furtherActionRequired === 1) {
            $rules['action_date'] = 'required|date_format:Y-m-d';
            $rules['action_time'] = 'required|date_format:H:i';
        }

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(apiErrorResponse($message), 422);
        }

        // ── Resolve FK nulls based on effective related_to ────────────────────
        $dealerId                  = $workNote->dealer_id;
        $customerId                = $workNote->customer_id;
        $customerRegisterRequestId = $workNote->customer_register_request_id;

        if ($request->has('related_to')) {
            // Reset all FKs when related_to changes
            $dealerId                  = null;
            $customerId                = null;
            $customerRegisterRequestId = null;

            if ($relatedTo === 'dealer') {
                $dealerId = (int) $request->input('dealer_id');
            } elseif ($relatedTo === 'customer') {
                $customerId = (int) $request->input('customer_id');
            } elseif ($relatedTo === 'customer_register_request') {
                $customerRegisterRequestId = (int) $request->input('customer_register_request_id');
            }
        }

        // ── Build update payload ──────────────────────────────────────────────
        $updateData = [];

        if ($request->has('date'))                         $updateData['date']                         = $request->input('date');
        if ($request->has('related_to'))                   $updateData['related_to']                   = $relatedTo;
        if ($request->has('related_to'))                   $updateData['dealer_id']                    = $dealerId;
        if ($request->has('related_to'))                   $updateData['customer_id']                  = $customerId;
        if ($request->has('related_to'))                   $updateData['customer_register_request_id'] = $customerRegisterRequestId;
        if ($request->has('subject'))                      $updateData['subject']                      = $request->input('subject');
        if ($request->has('activity_mode'))                $updateData['activity_mode']                = $request->input('activity_mode');
        if ($request->has('description'))                  $updateData['description']                  = $request->input('description');
        if ($request->has('key_take_away'))                $updateData['key_take_away']                = $request->input('key_take_away');
        if ($request->has('further_action_required'))      $updateData['further_action_required']      = $furtherActionRequired;
        if ($request->has('action_remarks'))               $updateData['action_remarks']               = $request->input('action_remarks');

        if ($furtherActionRequired === 1) {
            if ($request->has('action_date')) $updateData['action_date'] = $request->input('action_date');
            if ($request->has('action_time')) $updateData['action_time'] = $request->input('action_time');
        } else {
            // Clear action fields if further action no longer required
            $updateData['action_date'] = null;
            $updateData['action_time'] = null;
        }

        $workNote->update($updateData);

        // ── Append new attachments ────────────────────────────────────────────
        if ($request->hasFile('attachments')) {
            $voiceDir      = public_path('work_notes/voice');
            $attachmentDir = public_path('work_notes/attachments');

            if (!file_exists($voiceDir))      mkdir($voiceDir,      0775, true);
            if (!file_exists($attachmentDir)) mkdir($attachmentDir, 0775, true);

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

        $workNote->attachments->each(function ($attachment) {
            $folder = $attachment->type === 'voice_note' ? 'voice' : 'attachments';
            $attachment->file_url = url('work_notes/' . $folder . '/' . $attachment->file);
        });

        $result  = ['work_note' => $workNote];
        $message = "Work note updated successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    // ─── 5. Delete Single Attachment ─────────────────────────────────────────
    /**
     * DELETE /api/work-notes/{id}/attachments/{attachmentId}
     */
    public function destroyAttachment(Request $request, $id, $attachmentId)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $userId = $resp['user']['id'];

        // Verify ownership via the parent work note
        $workNote = WorkNote::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$workNote) {
            $message = "Work note not found.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $attachment = WorkNoteAttachment::where('id', $attachmentId)
            ->where('work_note_id', $id)
            ->first();

        if (!$attachment) {
            $message = "Attachment not found.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $folder   = $attachment->type === 'voice_note' ? 'voice' : 'attachments';
        $filePath = public_path('work_notes/' . $folder . '/' . $attachment->file);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $attachment->delete();

        $message = "Attachment deleted successfully.";
        return response()->json(apiSuccessResponse($message, []), 200);
    }

    // ─── 6. Delete Work Note ─────────────────────────────────────────────────

    public function destroy(Request $request, $id)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unauthorized. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $userId = $resp['user']['id'];

        $workNote = WorkNote::with(['attachments'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$workNote) {
            $message = "Work note not found.";
            return response()->json(apiErrorResponse($message), 422);
        }

        // ── Unlink all physical files ─────────────────────────────────────────
        foreach ($workNote->attachments as $attachment) {
            $folder   = $attachment->type === 'voice_note' ? 'voice' : 'attachments';
            $filePath = public_path('work_notes/' . $folder . '/' . $attachment->file);

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // ── Delete attachment records then work note ──────────────────────────
        $workNote->attachments()->delete();
        $workNote->delete();

        $message = "Work note deleted successfully.";
        return response()->json(apiSuccessResponse($message, []), 200);
    }
}