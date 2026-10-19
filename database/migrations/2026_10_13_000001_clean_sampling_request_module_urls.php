<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The sample request screens moved from one route with ?status= to five clean
 * URLs, so the sidebar entries have to point at the new paths.
 *
 * Only `view_route` is touched: the menu titles have been renamed by hand and
 * must be left alone.
 */
class CleanSamplingRequestModuleUrls extends Migration
{
    /** session_value => the route it should now point at. */
    const ROUTES = [
        'samplingRequestsAll'      => 'admin/sampling-requests',
        'samplingRequests'         => 'admin/sampling-requests/pending',
        'samplingRequestsApproved' => 'admin/sampling-requests/approved',
        'samplingRequestsClosed'   => 'admin/sampling-requests/closed',
        'samplingRequestsDeclined' => 'admin/sampling-requests/declined',
    ];

    const OLD_ROUTES = [
        'samplingRequestsAll'      => 'admin/sampling-requests?status=all',
        'samplingRequests'         => 'admin/sampling-requests?status=pending',
        'samplingRequestsApproved' => 'admin/sampling-requests?status=approved',
        'samplingRequestsClosed'   => 'admin/sampling-requests?status=closed',
        'samplingRequestsDeclined' => 'admin/sampling-requests?status=declined',
    ];

    public function up()
    {
        $this->apply(self::ROUTES);
    }

    public function down()
    {
        $this->apply(self::OLD_ROUTES);
    }

    private function apply(array $routes)
    {
        foreach ($routes as $sessionValue => $viewRoute) {
            DB::table('modules')
                ->where('session_value', $sessionValue)
                ->update(['view_route' => $viewRoute, 'updated_at' => now()]);
        }
    }
}
