<?php

namespace Tests\Feature;

use App\Module;
use App\User;
use App\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The admin panel's per-module permission check (App\Http\Middleware\User).
 *
 * Almost every edit screen in this app is routed as `.../{id?}`, and a module
 * row stores that route verbatim — `admin/add-edit-user/{id?}`. Anything that
 * rewrites or trims a module route before comparing it has to survive that
 * `?`, or every edit screen closes for every non-admin at once. That is what
 * these tests hold down.
 */
class AdminModulePermissionTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::where('type', '!=', 'admin')
            ->where('status', 1)
            ->where('web_access', 'Yes')
            ->firstOrFail();
    }

    /** The module whose edit_route carries an optional parameter. */
    private function employeesModule()
    {
        $module = Module::where('edit_route', 'like', '%{id?}%')->first();

        if (!$module) {
            $this->markTestSkipped('No module with an {id?} edit route.');
        }

        return $module;
    }

    /** Give the employee exactly these rights on a module for this test. */
    private function grant(Module $module, array $rights)
    {
        $rights += ['view_access' => 0, 'edit_access' => 0, 'delete_access' => 0];

        // The model guards these columns, so they are set one by one.
        $role = UserRole::firstOrNew(['user_id' => $this->employee->id, 'module_id' => $module->id]);
        foreach ($rights as $column => $value) {
            $role->{$column} = $value;
        }
        $role->save();
    }

    /** @return string where the request ended up */
    private function visit($url)
    {
        $response = $this->actingAs($this->employee)->get($url);

        return $response->isRedirect()
            ? (string) $response->headers->get('Location')
            : 'rendered';
    }

    /**
     * The regression: with edit rights, an {id?} edit screen opens.
     *
     * It once did not — a tidy-up of the route matching cut the string at the
     * first "?", turning `admin/add-edit-user/{id?}` into
     * `admin/add-edit-user/{id`, which matched nothing, and every non-admin
     * was bounced to the dashboard from every edit screen in the panel.
     */
    public function testAnEmployeeWithEditRightsCanOpenAnEditScreen()
    {
        $module = $this->employeesModule();
        $this->grant($module, ['view_access' => 1, 'edit_access' => 1]);

        // The stored route as an actual URL: admin/add-edit-user/{id?} → /10
        $url = '/' . str_replace('/{id?}', '/10', $module->edit_route);

        $this->assertNotContains('admin/dashboard', $this->visit($url),
            'an employee with edit rights must not be bounced off ' . $module->edit_route);
    }

    /** The listing behind the same module opens with view rights. */
    public function testAnEmployeeWithViewRightsCanOpenTheListing()
    {
        $module = $this->employeesModule();
        $this->grant($module, ['view_access' => 1]);

        $this->assertNotContains('admin/dashboard', $this->visit('/' . $module->view_route));
    }

    /** And the check still shuts what it is meant to shut. */
    public function testAnEmployeeWithoutViewRightsIsTurnedAway()
    {
        $module = $this->employeesModule();
        $this->grant($module, ['view_access' => 0, 'edit_access' => 0]);

        $this->assertContains('admin/dashboard', $this->visit('/' . $module->view_route),
            'no view access means no listing');
    }

    public function testAnEmployeeWithoutEditRightsIsTurnedAwayFromTheEditScreen()
    {
        $module = $this->employeesModule();
        $this->grant($module, ['view_access' => 1, 'edit_access' => 0]);

        $url = '/' . str_replace('/{id?}', '/10', $module->edit_route);

        $this->assertContains('admin/dashboard', $this->visit($url));
    }

    /**
     * Every module route in the database has to be reachable as written: the
     * check compares the stored route against the route URI, so a stored route
     * that is not a real route URI can never be matched and the module's
     * permissions would silently never apply.
     */
    public function testEveryModuleRouteMatchesARealRoute()
    {
        $uris = collect(\Route::getRoutes())->map(function ($route) {
            return $route->uri();
        })->flip();

        $unmatched = [];

        foreach (Module::where('status', 1)->get() as $module) {
            foreach (['view_route', 'edit_route', 'delete_route'] as $column) {
                $route = trim((string) $module->{$column});
                // A route with a query string is a menu link, not a route uri.
                if ($route === '' || strpos($route, '?') !== false && strpos($route, '{') === false) {
                    continue;
                }
                if (!$uris->has($route)) {
                    $unmatched[] = $module->id . ' ' . $column . ' = ' . $route;
                }
            }
        }

        $this->assertSame([], $unmatched,
            "These module routes do not exist as routes, so their permissions never apply:\n"
            . implode("\n", $unmatched));
    }
}
