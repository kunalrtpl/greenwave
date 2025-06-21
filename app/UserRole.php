<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    //

    public static function checkExtraPermission($moduleId,$permissionKey){
        // Inputs:
        if(auth()->user()->type == "admin"){
            return true;
        }
        $userId = auth()->id(); // or any user id

        // Get the user role for this module:
        $userRole = UserRole::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->first();

        if (!$userRole) {
            // No role assigned for this module
            $hasAccess = false;
        } else {
            $extraPermissions = json_decode($userRole->extra_permissions, true) ?? [];
            $hasAccess = isset($extraPermissions[$permissionKey]) && $extraPermissions[$permissionKey] == 1;
        }

        if ($hasAccess) {
            return true;
        } else {
            return false;
        }

    }
}
