<?php

namespace Tests\Feature;

use App\AuthToken;
use App\User;
use App\UserScheduler;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * GET /api/executive/v2/schedulers — the status filter.
 *
 * The app asks for several statuses at once (status=Pending,Reminder), so the
 * filter has to read a list while a single value keeps working exactly as it
 * did for the builds already in the field.
 */
class SchedulerStatusFilterTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $executive;

    /** @var string */
    private $token;

    /** @var string */
    private $date = '2026-11-18';

    protected function setUp(): void
    {
        parent::setUp();

        $this->executive = User::where('status', 1)
            ->where('app_access', 'Yes')
            ->whereNotNull('mobile')
            ->firstOrFail();

        $this->token = encrypt('user##-' . $this->executive->mobile);
        AuthToken::create(['type' => 'user', 'user_id' => $this->executive->id, 'auth_token' => $this->token]);
    }

    private function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json'];
    }

    /** One scheduler of each status, on a date of its own so nothing else shows up. */
    private function makeSchedulers(array $statuses)
    {
        $ids = [];

        foreach ($statuses as $i => $status) {
            $ids[$status] = UserScheduler::create([
                'user_id'        => $this->executive->id,
                'related_to'     => 'dealer',
                'subject'        => 'Filter test ' . $status,
                'scheduler_date' => $this->date,
                'scheduler_time' => sprintf('%02d:00', 9 + $i),
                'description'    => 'Filter test ' . $status,
                'status'         => $status,
            ])->id;
        }

        return $ids;
    }

    /** The ids the endpoint returns for a given query. */
    private function fetch(array $query)
    {
        $res = $this->getJson(
            '/api/executive/v2/schedulers?' . http_build_query($query + ['date' => $this->date]),
            $this->headers()
        );

        $res->assertStatus(200);

        return array_map(function ($row) {
            return $row['id'];
        }, $res->json('data.schedulers'));
    }

    public function testASingleStatusStillFiltersToThatOneStatus()
    {
        $ids = $this->makeSchedulers(['Pending', 'Done', 'Reminder']);

        $this->assertSame([$ids['Pending']], $this->fetch(['status' => 'Pending']));
    }

    public function testCommaSeparatedStatusesReturnAllOfThem()
    {
        $ids = $this->makeSchedulers(['Pending', 'Done', 'Reminder', 'Closed']);

        $got = $this->fetch(['status' => 'Pending,Reminder']);

        sort($got);
        $want = [$ids['Pending'], $ids['Reminder']];
        sort($want);

        $this->assertSame($want, $got);
    }

    /** Spaces around the commas are what a hand-built query string looks like. */
    public function testSpacesAndEmptiesInTheListAreIgnored()
    {
        $ids = $this->makeSchedulers(['Pending', 'Done', 'Reminder']);

        $got = $this->fetch(['status' => ' Pending , , Done ,']);

        sort($got);
        $want = [$ids['Pending'], $ids['Done']];
        sort($want);

        $this->assertSame($want, $got);
    }

    /** status[]=Pending&status[]=Done — the other shape an HTTP client produces. */
    public function testAnArrayOfStatusesWorksToo()
    {
        $ids = $this->makeSchedulers(['Pending', 'Done', 'Reminder']);

        $got = $this->fetch(['status' => ['Pending', 'Done']]);

        sort($got);
        $want = [$ids['Pending'], $ids['Done']];
        sort($want);

        $this->assertSame($want, $got);
    }

    public function testNoStatusReturnsEveryStatus()
    {
        $ids = $this->makeSchedulers(['Pending', 'Done', 'Reminder']);

        $got = $this->fetch([]);

        sort($got);
        $want = array_values($ids);
        sort($want);

        $this->assertSame($want, $got);
    }

    /** An empty status is not a filter — it must not hide everything. */
    public function testAnEmptyStatusIsNotAFilter()
    {
        $ids = $this->makeSchedulers(['Pending', 'Done']);

        $got = $this->fetch(['status' => '']);

        sort($got);
        $want = array_values($ids);
        sort($want);

        $this->assertSame($want, $got);
    }

    /** A status nobody uses returns nothing rather than everything. */
    public function testAnUnknownStatusReturnsNothing()
    {
        $this->makeSchedulers(['Pending', 'Done']);

        $this->assertSame([], $this->fetch(['status' => 'NotAStatus']));
    }
}
