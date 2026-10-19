<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Moves "New Dispatch" out of the listing's page header and into the sidebar,
 * next to the dispatch history it belongs with.
 */
class AddNewDispatchModule extends Migration
{
    const SESSION_VALUE = 'samplingDispatchCreate';

    public function up()
    {
        $parentId = DB::table('modules')->where('name', 'Sampling Management')->value('id');
        if (!$parentId) {
            return;
        }

        DB::table('modules')->updateOrInsert(
            ['session_value' => self::SESSION_VALUE],
            [
                'name'           => 'New Dispatch',
                'parent_id'      => $parentId,
                'view_route'     => 'admin/sample-dispatch/create',
                'edit_route'     => '',
                'delete_route'   => '',
                'icon'           => '',
                'status'         => 1,
                // Immediately after "Sample Dispatch" (sortorder 3).
                'sortorder'      => 4,
                'shown_in_roles' => 1,
                'table_name'     => '',
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );

        // Push the two request archives down so the new entry keeps its place.
        DB::table('modules')->where('session_value', 'samplingRequestsClosed')->update(['sortorder' => 5]);
        DB::table('modules')->where('session_value', 'samplingRequestsDeclined')->update(['sortorder' => 6]);

        $parent = DB::table('modules')->where('id', $parentId)->first();
        $sessionValues = array_filter(explode(',', $parent->session_value));

        if (!in_array(self::SESSION_VALUE, $sessionValues)) {
            $sessionValues[] = self::SESSION_VALUE;
            DB::table('modules')->where('id', $parentId)->update([
                'session_value' => implode(',', $sessionValues),
                'updated_at'    => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('modules')->where('session_value', self::SESSION_VALUE)->delete();
        DB::table('modules')->where('session_value', 'samplingRequestsClosed')->update(['sortorder' => 4]);
        DB::table('modules')->where('session_value', 'samplingRequestsDeclined')->update(['sortorder' => 5]);
    }
}
