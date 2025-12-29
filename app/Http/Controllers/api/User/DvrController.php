<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\UserDvr;
use App\UserDvrProduct;
use App\UserDvrTrial;
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
            'trials',
            'customerContacts',
            'attachments',
            'complaint_sample',
            'market_sample',
            'sample_submission',
            'user_scheduler',
            'customer_contact_info'
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
            'dvr_date' => 'required|date',
            //'products' => 'required|array',
            //'products.*' => 'required|integer'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {
            $userId = $this->resp['user']['id'];

            $dvr = isset($data['id'])
                ? UserDvr::where('id', $data['id'])->where('user_id', $userId)->first()
                : new UserDvr();

            if (!$dvr) {
                return response()->json(apiErrorResponse('DVR not found'), 404);
            }

            $dvr->fill($request->except(['products']));
            $dvr->user_id = $userId;
            $dvr->save();

            // Save products
            if(isset($data['products'])){
            	UserDvrProduct::where('user_dvr_id', $dvr->id)->delete();
	            foreach ($data['products'] as $productId) {
	                UserDvrProduct::create([
	                    'user_dvr_id' => $dvr->id,
	                    'product_id' => $productId
	                ]);
	            }
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('DVR saved successfully', ['dvr' => $dvr]),
                200
            );

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    /**
     * 4️⃣ ADD SINGLE TRIAL WITH PRODUCTS
     * POST /api/user/dvr/trial/add
     */
    public function addTrial(Request $request)
    {
        $data = $request->all();

        $rules = [
            'user_dvr_id' => 'required|integer|exists:user_dvrs,id',
            'products'   => 'nullable|array',
            'products.*' => 'integer|exists:products,id'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {

            // ✅ Extract products and REMOVE from main data
            $products = $data['products'] ?? [];
            unset($data['products']);

            // ✅ Create trial safely
            $trial = UserDvrTrial::create($data);

            // ✅ Insert products mapping
            if (!empty($products)) {

                $rows = [];

                foreach ($products as $productId) {
                    $rows[] = [
                        'user_dvr_id'       => $data['user_dvr_id'],
                        'user_dvr_trial_id' => $trial->id,
                        'product_id'        => $productId,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                UserDvrProduct::insert($rows);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Trial added successfully', [
                    'trial'    => $trial,
                    'products' => $products
                ]),
                200
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5️⃣ UPDATE SINGLE TRIAL WITH PRODUCTS
     * POST /api/user/dvr/trial/update
     */
    public function updateTrial(Request $request)
    {
        $data = $request->all();

        $rules = [
            'id'          => 'required|integer|exists:user_dvr_trials,id',
            'user_dvr_id' => 'required|integer|exists:user_dvrs,id',
            'products'   => 'nullable|array',
            'products.*' => 'integer|exists:products,id'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();

        try {

            // ✅ Fetch Trial
            $trial = UserDvrTrial::findOrFail($data['id']);

            // ✅ Extract products & remove from update data
            $products = $data['products'] ?? [];
            unset($data['products'], $data['id']);

            // ✅ Update trial
            $trial->update($data);

            // ✅ Delete old product mappings for this trial
            UserDvrProduct::where('user_dvr_trial_id', $trial->id)->delete();

            // ✅ Re-insert products
            if (!empty($products)) {

                $rows = [];

                foreach ($products as $productId) {
                    $rows[] = [
                        'user_dvr_id'       => $data['user_dvr_id'],
                        'user_dvr_trial_id' => $trial->id,
                        'product_id'        => $productId,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                UserDvrProduct::insert($rows);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Trial updated successfully', [
                    'trial'    => $trial->fresh(),
                    'products' => $products
                ]),
                200
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
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
	    $data = $request->all();

	    $rules = [
	        'user_dvr_id' => 'required|integer',
	        'user_dvr_trial_id' => 'nullable|integer',
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

	    $attachment = UserDvrAttachment::create([
	        'user_dvr_id'       => $request->user_dvr_id,
	        'user_dvr_trial_id' => $request->user_dvr_trial_id ?? null,
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
}
