<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\User;
use Validator;
use DB;

/**
 * Class AppRoleAssignmentController
 * * Handles retrieval and assignment of app roles for users.
 */
class AppRoleAssignmentController extends Controller
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
     * 1️⃣ GET USER APP ROLES
     * POST /api/user/roles/get
     * * Returns roles from 'app_roles' table based on user's comma-separated list.
     */
    public function getUserAppRoles(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        // If user_id is provided in request, use it; otherwise use ID from token
        $userId = $request->input('user_id', $this->resp['user']['id']);

        // Fetch the user's current app_roles string
        $user = DB::table('users')->where('id', $userId)->first(['app_roles']);

        if (!$user) {
            return response()->json(apiErrorResponse('User not found'), 404);
        }

        // Explode comma-separated keys into an array
        $assignedKeys = array_filter(explode(',', $user->app_roles));

        // Fetch role details from app_roles table where type is 'executive'
        $roles = DB::table('app_roles')
            ->where('type', 'executive')
            ->whereIn('key', $assignedKeys)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json(
            apiSuccessResponse('User app roles fetched', [
                'user_id' => $userId,
                'assigned_roles' => $roles
            ]),
            200
        );
    }

    /**
     * 2️⃣ SAVE APP ROLES
     * POST /api/user/roles/save
     * * Saves an array of role keys as a comma-separated string in users table.
     */
    public function saveAppRoles(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'user_id' => 'nullable|integer|exists:users,id',
            'role_keys' => 'required|array', // Expected: ["product_list", "task_scheduler"]
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        try {
            $userId = $request->input('user_id', $this->resp['user']['id']);
            
            // Convert array to comma-separated string
            $rolesString = implode(',', $request->role_keys);

            // Update the user record
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'app_roles' => $rolesString,
                    'updated_at' => now()
                ]);

            return response()->json(
                apiSuccessResponse('App roles updated successfully', [
                    'user_id' => $userId,
                    'app_roles' => $rolesString
                ]),
                200
            );

        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }
}