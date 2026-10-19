<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `modules.session_value` is varchar(191). The sampling parent's list of child
 * session values had grown past that and was silently truncated mid-word, so
 * the entries that fell off the end — "Sample Dispatch Entry" among them — no
 * longer lit up "Sampling Management" in the sidebar.
 *
 * Rebuilt from the children that actually exist, with the live ones first so a
 * future overflow drops a disabled entry rather than a working one.
 */
class RepairSamplingParentSessionValues extends Migration
{
    const COLUMN_LIMIT = 191;

    public function up()
    {
        $parents = DB::table('modules')->where('parent_id', 'ROOT')->get(['id']);

        foreach ($parents as $parent) {
            $children = DB::table('modules')
                ->where('parent_id', (string) $parent->id)
                ->orderBy('status', 'DESC')
                ->orderBy('sortorder')
                ->pluck('session_value')
                ->filter()
                ->unique();

            if ($children->isEmpty()) {
                continue;
            }

            $value = '';
            foreach ($children as $sessionValue) {
                $next = $value === '' ? $sessionValue : $value . ',' . $sessionValue;
                if (strlen($next) > self::COLUMN_LIMIT) {
                    break;
                }
                $value = $next;
            }

            DB::table('modules')->where('id', $parent->id)->update([
                'session_value' => $value,
                'updated_at'    => now(),
            ]);
        }
    }

    public function down()
    {
        // The previous values were already truncated, so there is nothing
        // meaningful to restore.
    }
}
