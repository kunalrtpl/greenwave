<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "All Sample Requests" sidebar entry the client asked for: the same
 * listing screen with ?status=all, which shows every request regardless of
 * status and offers a status dropdown of its own.
 *
 * It is listed first so the desk can start from the whole picture and drill
 * into a status from there.
 */
class AddAllSamplingRequestsModule extends Migration
{
    const SESSION_VALUE = 'samplingRequestsAll';

    public function up()
    {
        $parentId = DB::table('modules')->where('name', 'Sampling Management')->value('id');
        if (!$parentId) {
            return;
        }

        DB::table('modules')->updateOrInsert(
            ['session_value' => self::SESSION_VALUE],
            [
                'name'           => 'All Sample Requests',
                'parent_id'      => $parentId,
                'view_route'     => 'admin/sampling-requests?status=all',
                'edit_route'     => '',
                'delete_route'   => '',
                'icon'           => '',
                'status'         => 1,
                'sortorder'      => 0,
                'shown_in_roles' => 1,
                'table_name'     => '',
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );

        // The parent lights up for any of its children.
        $parent = DB::table('modules')->where('id', $parentId)->first();
        $sessionValues = array_filter(explode(',', $parent->session_value));

        if (!in_array(self::SESSION_VALUE, $sessionValues)) {
            array_unshift($sessionValues, self::SESSION_VALUE);
            DB::table('modules')->where('id', $parentId)->update([
                'session_value' => implode(',', $sessionValues),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('modules')->where('session_value', self::SESSION_VALUE)->delete();
    }
}
