<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\UserDvr;
use App\UserDvrProduct;
use App\Trial;
use App\UserDvrTrialLink;
use App\UserDvrCustomerContact;
use App\UserDvrAttachment;
use Validator;
use DB;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;

/**
 * Class DvrController
 *
 * Handles DVR APIs for authenticated users via custom token
 */
class DvrController extends Controller
{
    protected $resp;

    /**
     * Verify token from Authorization header
     */
    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    /**
     * Base DVR query with relations
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseDvrQuery()
    {
        return UserDvr::with([
            'customer',
            'customer_register_request',
            'products',
              // Trials + their own data
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
            'query_info'
        ]);
    }

	/**
	 * 1️⃣ DVR LISTING (WITH MONTH/YEAR FILTER)
	 * GET /api/user/dvrs?month={m}&year={y}
	 */
	public function dvrs(Request $request)
	{
	    if (!$this->resp['status'] || !isset($this->resp['user'])) {
	        return response()->json(apiErrorResponse('Unauthorized'), 401);
	    }

	    $userId = $this->resp['user']['id'];

	    // ✅ Month & Year fallback
	    $month = $request->query('month', now()->month);
	    $year  = $request->query('year', now()->year);

	    // ✅ Validate month/year
	    if ($month < 1 || $month > 12 || $year < 2000) {
	        return response()->json(apiErrorResponse('Invalid month or year'), 422);
	    }

	    $dvrs = $this->baseDvrQuery()
	        ->where('user_id', $userId)
	        ->whereMonth('dvr_date', $month)
	        ->whereYear('dvr_date', $year)
	        ->orderBy('dvr_date', 'desc')
	        ->get();

	    return response()->json(
	        apiSuccessResponse('DVR list fetched', ['dvrs' => $dvrs]),
	        200
	    );
	}


    /**
     * DVR DETAIL API
     * GET /api/user/dvr/detail/{id}
     */
    public function dvrDetail($id)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        if (!is_numeric($id)) {
            return response()->json(apiErrorResponse('Invalid DVR ID'), 422);
        }

        $userId = $this->resp['user']['id'];

        $dvr = $this->baseDvrQuery()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$dvr) {
            return response()->json(apiErrorResponse('DVR not found'), 404);
        }

        return response()->json(
            apiSuccessResponse('DVR detail fetched', ['dvr' => $dvr]),
            200
        );
    }

    /**
     * 2️⃣ LATEST DVR API
     * GET /api/user/dvr/latest
     */
    public function latestDvr(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];

        $dvr = $this->baseDvrQuery()
            ->where('user_id', $userId)
            ->orderBy('dvr_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$dvr) {
            return response()->json(apiErrorResponse('No DVR found'), 404);
        }

        return response()->json(
            apiSuccessResponse('Latest DVR fetched', ['dvr' => $dvr]),
            200
        );
    }


    /**
     * 2️⃣ ADD / EDIT DVR (COMMON)
     * POST /api/user/dvr/save
     */
    public function saveDvr(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $data = $request->all();

        $rules = [
            'dvr_date'   => 'required|date',
            'trial_ids'  => 'nullable|array',
            'trial_ids.*'=> 'integer|exists:trials,id',
            'products'   => 'nullable|array',
            'products.*' => 'integer|exists:products,id'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {

            $userId = $this->resp['user']['id'];

            $isNew = empty($data['id']);

            // 🔹 CREATE OR FETCH DVR
            $dvr = $isNew
                ? new UserDvr()
                : UserDvr::where('id', $data['id'])
                    ->where('user_id', $userId)
                    ->first();

            if (!$dvr) {
                return response()->json(apiErrorResponse('DVR not found'), 404);
            }

            // 🔹 SAVE DVR
            $dvr->fill($request->except(['products','trial_ids','id']));
            $dvr->user_id = $userId;
            $dvr->save();

            // 🔹 DVR PRODUCTS (DVR LEVEL ONLY)
            if (isset($data['products'])) {

                UserDvrProduct::where('user_dvr_id', $dvr->id)
                    ->whereNull('trial_id')
                    ->delete();

                foreach ($data['products'] as $productId) {
                    UserDvrProduct::create([
                        'user_dvr_id' => $dvr->id,
                        'product_id'  => $productId
                    ]);
                }
            }

            // 🔥 LINK TRIALS (ONLY FOR NEW DVR)
            if ($isNew && !empty($data['trial_ids'])) {

                $rows = [];

                foreach (array_unique($data['trial_ids']) as $trialId) {
                    $rows[] = [
                        'user_dvr_id' => $dvr->id,
                        'trial_id'    => $trialId,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }

                DB::table('user_dvr_trial_links')->insert($rows);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse(
                    $isNew ? 'DVR created successfully' : 'DVR updated successfully',
                    ['dvr' => $dvr->load('trials')]
                ),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    /**
     * 4️⃣ ADD SINGLE TRIAL WITH PRODUCTS
     * POST /api/user/dvr/trial/add
     */
    public function addTrial(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'user_dvr_id' => 'required|integer|exists:user_dvrs,id',
            'products'    => 'nullable|array',
            'products.*'  => 'integer|exists:products,id'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {
           $userId = $this->resp['user']['id'];

            // 1️⃣ Create Trial
            $trial = Trial::create(
                array_merge(
                    $request->except(['products', 'user_dvr_id']),
                    ['user_id' => $userId]
                )
            );

            // 2️⃣ Link DVR ↔ Trial
            DB::table('user_dvr_trial_links')->insert([
                'user_dvr_id' => $request->user_dvr_id,
                'trial_id'    => $trial->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // 3️⃣ Attach products
            foreach ($request->products ?? [] as $productId) {
                UserDvrProduct::create([
                    'user_dvr_id'       => null,
                    'trial_id' => $trial->id,
                    'product_id'        => $productId
                ]);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Trial added successfully', $trial),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    /**
     * 5️⃣ UPDATE TRIAL WITH PRODUCTS
     * POST /api/user/dvr/trial/update
     */
    public function updateTrial(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'id'          => 'required|integer|exists:trials,id',
            'products'    => 'nullable|array',
            'products.*'  => 'integer|exists:products,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {
            $userId = $this->resp['user']['id'];

            /** 1️⃣ Fetch Trial */
            $trial = Trial::where('id', $request->id)
                          ->where('user_id', $userId)
                          ->firstOrFail();

            /** 2️⃣ Update Trial Fields */
            $trial->update(
                $request->except(['id', 'products'])
            );

            /** 3️⃣ Update DVR ↔ Trial Link (if sent) */
            if ($request->filled('user_dvr_id')) {
                DB::table('user_dvr_trial_links')
                    ->updateOrInsert(
                        ['trial_id' => $trial->id],
                        [
                            'user_dvr_id' => $request->user_dvr_id,
                            'updated_at'  => now(),
                            'created_at'  => now(),
                        ]
                    );
            }

            /** 4️⃣ Remove old products */
            UserDvrProduct::where('trial_id', $trial->id)->delete();

            /** 5️⃣ Attach new products */
            foreach ($request->products ?? [] as $productId) {
                UserDvrProduct::create([
                    'user_dvr_id' => null,
                    'trial_id'    => $trial->id,
                    'product_id'  => $productId
                ]);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Trial updated successfully', $trial->fresh()),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }



    /**
     * 8️⃣ GET USER TRIALS BY LAST N DAYS
     * POST /api/user/trials/by-days
     */
    public function trialsByDays(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        // ✅ Validation (days optional)
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer|min:1|max:365'
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        $userId = $this->resp['user']['id'];

        // 🔁 Fallback to 30 days if not provided or empty
        $days = (int) ($request->days ?? 30);
        if ($days <= 0) {
            $days = 30;
        }

        // 📅 Date range
        $toDate   = Carbon::today()->endOfDay();
        $fromDate = Carbon::today()->subDays($days - 1)->startOfDay();

        // 🔍 Fetch trials
        $trials = Trial::with(['products','attachments','complaint_info','other_team_member_info'])->where('user_id', $userId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            apiSuccessResponse(
                'Trials fetched successfully',
                [
                    'from_date'    => $fromDate->toDateString(),
                    'to_date'      => $toDate->toDateString(),
                    'days'         => $days,
                    'total_trials' => $trials->count(),
                    'trials'       => $trials
                ]
            ),
            200
        );
    }


    /**
     * 5️⃣ ADD / REPLACE MULTIPLE CONTACT PERSONS
     * POST /api/user/dvr/contacts/add
     */
    public function addContacts(Request $request)
    {
        $data = $request->all();

        // ✅ Validation Rules
        $rules = [
            'user_dvr_id'             => 'required|integer',
            'customer_contact_ids'   => 'nullable|array',
            'customer_contact_ids.*' => 'integer',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {
            // ✅ 1. Always delete existing contacts for this DVR
            UserDvrCustomerContact::where(
                'user_dvr_id',
                $data['user_dvr_id']
            )->delete();

            // ✅ 2. If contact IDs are provided, insert them
            if (!empty($data['customer_contact_ids'])) {

                $insertData = [];

                foreach (array_unique($data['customer_contact_ids']) as $contactId) {
                    $insertData[] = [
                        'user_dvr_id'          => $data['user_dvr_id'],
                        'customer_contact_id' => $contactId,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                }

                UserDvrCustomerContact::insert($insertData);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Contacts updated successfully', []),
                200
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(
                apiErrorResponse('Failed to update contacts', [
                    'error' => $e->getMessage()
                ]),
                500
            );
        }
    }



	/**
	 * ADD SINGLE ATTACHMENT (FILE UPLOAD)
	 */
	public function addAttachment(Request $request)
	{
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

	    $data = $request->all();

	    $rules = [
	        'user_dvr_id' => 'nullable|integer',
	        'trial_id' => 'nullable|integer',
	        'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
	    ];

	    $validator = Validator::make($data, $rules);
	    if ($validator->fails()) {
	        return response()->json(validationResponse($validator), 422);
	    }

	    $fileName = null;

	    if ($request->hasFile('file')) {
	        if (Input::file('file')->isValid()) {

	            $file = Input::file('file');

	            //  Dynamic folder per DVR
	            $destination = 'DvrAttachments/' . $request->user_dvr_id . '/';

	            // Create directory if not exists
	            if (!file_exists(public_path($destination))) {
	                mkdir(public_path($destination), 0777, true);
	            }

	            $ext = $file->getClientOriginalExtension();
	            $fileName = 'dvr_attachment_' . uniqid() . '_' . date('H-i-s') . '.' . $ext;

	            $file->move(public_path($destination), $fileName);
	        }
	    }

        $userId = $this->resp['user']['id'];

	    $attachment = UserDvrAttachment::create([
	        'user_id'           => $userId,
            'user_dvr_id'       => $request->user_dvr_id,
	        'trial_id' => $request->trial_id ?? null,
	        'type'              => $request->type ?? null,
	        'label'             => $request->label ?? null,
	        'file'              => $fileName,
	        'share'             => $request->share ?? 0
	    ]);

	    return response()->json(
	        apiSuccessResponse('Attachment uploaded successfully', ['attachment' => $attachment]),
	        200
	    );
	}

    /**
     * 6️⃣ DELETE DVR
     * POST /api/user/v2/dvr/delete
     */
    public function deleteDvr(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $data = $request->all();

        $rules = [
            'id' => 'required|integer|exists:user_dvrs,id',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {
            $userId = $this->resp['user']['id'];

            // ✅ Fetch DVR belonging to logged-in user
            $dvr = UserDvr::where('id', $data['id'])
                          ->where('user_id', $userId)
                          ->first();

            if (!$dvr) {
                return response()->json(apiErrorResponse('DVR not found'), 404);
            }

            /**
             * IMPORTANT:
             * - Trials, products, contacts, attachments
             * - Will be auto-deleted if FK cascade exists
             * - Otherwise we clean manually (safe)
             */

            // 🔥 Delete attachments files from storage
            foreach ($dvr->attachments as $attachment) {
                $filePath = public_path(
                    'DvrAttachments/' . $dvr->id . '/' . $attachment->file
                );

                if ($attachment->file && file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // 🧹 Manual cleanup (safe even if cascade exists)
            UserDvrProduct::where('user_dvr_id', $dvr->id)->delete();
            UserDvrTrial::where('user_dvr_id', $dvr->id)->delete();
            UserDvrCustomerContact::where('user_dvr_id', $dvr->id)->delete();
            UserDvrAttachment::where('user_dvr_id', $dvr->id)->delete();

            // ✅ Finally delete DVR
            $dvr->delete();

            DB::commit();

            return response()->json(
                apiSuccessResponse('DVR deleted successfully'),
                200
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(
                apiErrorResponse('Failed to delete DVR', [
                    'error' => $e->getMessage()
                ]),
                500
            );
        }
    }


    /**
     * 7️⃣ DELETE SINGLE ATTACHMENT
     * POST /api/user/dvr/attachment/delete
     */
    public function deleteAttachment(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'id' => 'required|integer|exists:user_dvr_attachments,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        try {
            $userId = $this->resp['user']['id'];

            // 🔍 Fetch attachment with DVR ownership check
            $attachment = UserDvrAttachment::with('dvr')
                ->where('id', $request->id)
                ->first();

            if (
                !$attachment ||
                !$attachment->dvr ||
                $attachment->dvr->user_id !== $userId
            ) {
                return response()->json(apiErrorResponse('Attachment not found'), 404);
            }

            // 🧹 Delete physical file
            if ($attachment->file) {
                $filePath = public_path(
                    'DvrAttachments/' .
                    $attachment->user_dvr_id . '/' .
                    $attachment->file
                );

                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // 🗑️ Delete DB record
            $attachment->delete();

            return response()->json(
                apiSuccessResponse('Attachment deleted successfully'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                apiErrorResponse('Failed to delete attachment', [
                    'error' => $e->getMessage()
                ]),
                500
            );
        }
    }
}
