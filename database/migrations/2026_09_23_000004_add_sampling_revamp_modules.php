<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Swaps the sidebar over to the revamped two-step sampling flow.
 *
 * The four status screens are one controller action filtered by ?status=, so
 * they are four `modules` rows pointing at the same route with a different
 * query string — that is what makes them individually permissionable in
 * user_roles.
 *
 * The superseded rows are only deactivated (status = 0), never deleted, so the
 * old screens stay reachable for looking up historical data and any user_roles
 * rows pointing at them remain valid.
 */
class AddSamplingRevampModules extends Migration
{
    /** session_value => [name, view_route, sortorder] */
    const NEW_MODULES = [
        'samplingRequests'          => ['Sample Requests',       'admin/sampling-requests?status=pending',  1],
        'samplingRequestsApproved'  => ['Approved / To Dispatch', 'admin/sampling-requests?status=approved', 2],
        'samplingDispatch'          => ['Sample Dispatch',       'admin/sample-dispatch',                   3],
        'samplingRequestsClosed'    => ['Closed Requests',       'admin/sampling-requests?status=closed',   4],
        'samplingRequestsDeclined'  => ['Declined Requests',     'admin/sampling-requests?status=declined', 5],
    ];

    /** Screens the two-step flow replaces. */
    const SUPERSEDED_SESSION_VALUES = [
        'freeSampling',
        'samplingDispatchPlanning',
        'sampleFinalizeDo',
        'sampleDoReady',
        'sampleBillReady',
        'sampleDispatchedMaterial',
    ];

    public function up()
    {
        $parentId = DB::table('modules')->where('name', 'Sampling Management')->value('id');
        if (!$parentId) {
            return;
        }

        DB::table('modules')
            ->whereIn('session_value', self::SUPERSEDED_SESSION_VALUES)
            ->update(['status' => 0, 'updated_at' => now()]);

        foreach (self::NEW_MODULES as $sessionValue => $module) {
            list($name, $viewRoute, $sortOrder) = $module;

            DB::table('modules')->updateOrInsert(
                ['session_value' => $sessionValue],
                [
                    'name'           => $name,
                    'parent_id'      => $parentId,
                    'view_route'     => $viewRoute,
                    'edit_route'     => '',
                    'delete_route'   => '',
                    'icon'           => '',
                    'status'         => 1,
                    'sortorder'      => $sortOrder,
                    'shown_in_roles' => 1,
                    'table_name'     => '',
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ]
            );
        }

        DB::table('modules')
            ->where('id', $parentId)
            ->update([
                'session_value' => implode(',', array_merge(
                    array_keys(self::NEW_MODULES),
                    self::SUPERSEDED_SESSION_VALUES,
                    ['paidSampling', 'samplingRequestDetail']
                )),
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        DB::table('modules')->whereIn('session_value', array_keys(self::NEW_MODULES))->delete();
        DB::table('modules')->whereIn('session_value', self::SUPERSEDED_SESSION_VALUES)->update(['status' => 1]);
    }
}
