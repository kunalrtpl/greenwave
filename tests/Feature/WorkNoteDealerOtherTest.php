<?php

namespace Tests\Feature;

use App\AuthToken;
use App\WorkNote;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * POST /api/executive/work-notes with the three "other dealer" fields.
 *
 * A work note about a dealer who is not on the system has no dealer_id to
 * point at, so the app sends the name, mobile and address as plain text
 * instead. All three are optional and may arrive on their own.
 *
 * The rule that matters most here: builds already in the field never send
 * them, and must keep working exactly as before.
 */
class WorkNoteDealerOtherTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\User */
    private $executive;

    /** @var string */
    private $token;

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

    /** The payload exactly as the app sends it. */
    private function payload()
    {
        return [
            'date'                    => '2026-07-25',
            'related_to'              => 'dealer',
            'activity_mode'           => 'interaction',
            'dealer_other'            => 'Rajesh Prasad',
            'dealer_other_mobile'     => '9408730419',
            'dealer_other_address'    => 'Saregam',
            'description'             => 'Call Activity  Tomorrow shall visit to Orbit and able to collect competition data for polyester knit',
            'key_take_away'           => 'Dealership opportunity',
            'further_action_required' => '1',
            'action_date'             => '2026-07-27',
            'action_time'             => '11:30',
            'action_remarks'          => 'confirm information',
        ];
    }

    /* ─────────────────────────── Creating ─────────────────────────── */

    public function testTheNewFieldsAreSaved()
    {
        $id = $this->postJson('/api/executive/work-notes', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $note = WorkNote::findOrFail($id);

        $this->assertSame('Rajesh Prasad', $note->dealer_other);
        $this->assertSame('9408730419', $note->dealer_other_mobile);
        $this->assertSame('Saregam', $note->dealer_other_address);

        // The rest of the note is unaffected.
        $this->assertSame('dealer', $note->related_to);
        $this->assertNull($note->dealer_id, 'this dealer is not on the system');
        $this->assertSame(1, $note->further_action_required);
    }

    /** They come back on the read endpoints, so the app can show them again. */
    public function testTheNewFieldsAreReturnedByTheApi()
    {
        $id = $this->postJson('/api/executive/work-notes', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $this->getJson('/api/executive/work-notes/' . $id, $this->headers())
            ->assertStatus(200)
            ->assertJson(['data' => ['work_note' => [
                'dealer_other'         => 'Rajesh Prasad',
                'dealer_other_mobile'  => '9408730419',
                'dealer_other_address' => 'Saregam',
            ]]]);
    }

    /* ────────────────── Builds already in the field ────────────────── */

    /** The old payload, with none of the three fields, still saves. */
    public function testAnOldPayloadWithoutTheNewFieldsStillWorks()
    {
        $old = $this->payload();
        unset($old['dealer_other'], $old['dealer_other_mobile'], $old['dealer_other_address']);

        $id = $this->postJson('/api/executive/work-notes', $old, $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $note = WorkNote::findOrFail($id);

        $this->assertNull($note->dealer_other);
        $this->assertNull($note->dealer_other_mobile);
        $this->assertNull($note->dealer_other_address);
        $this->assertSame('Dealership opportunity', $note->key_take_away);
    }

    /** They are independent — one on its own is fine. */
    public function testOnlyOneOfThemIsEnough()
    {
        $partial = $this->payload();
        unset($partial['dealer_other_mobile'], $partial['dealer_other_address']);

        $id = $this->postJson('/api/executive/work-notes', $partial, $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $note = WorkNote::findOrFail($id);

        $this->assertSame('Rajesh Prasad', $note->dealer_other);
        $this->assertNull($note->dealer_other_mobile);
    }

    /** A registered dealer is unaffected: dealer_id still saves on its own. */
    public function testARegisteredDealerStillSavesItsId()
    {
        $dealer = \App\Dealer::where('status', 1)->firstOrFail();

        $note = $this->payload();
        unset($note['dealer_other'], $note['dealer_other_mobile'], $note['dealer_other_address']);
        $note['dealer_id'] = $dealer->id;

        $id = $this->postJson('/api/executive/work-notes', $note, $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $this->assertEquals($dealer->id, WorkNote::findOrFail($id)->dealer_id);
    }

    /* ─────────────────────────── Editing ─────────────────────────── */

    public function testTheFieldsCanBeEditedAndCleared()
    {
        $id = $this->postJson('/api/executive/work-notes', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        // The action fields come along because this note still has
        // further_action_required = 1, which the endpoint has always insisted
        // on — that rule is older than these three columns.
        $this->postJson('/api/executive/work-notes/' . $id, [
            'dealer_other'        => 'Rajesh Prasad & Sons',
            'dealer_other_mobile' => '',
            'action_date'         => '2026-07-27',
            'action_time'         => '11:30',
        ], $this->headers())->assertStatus(200);

        $note = WorkNote::findOrFail($id);

        $this->assertSame('Rajesh Prasad & Sons', $note->dealer_other);
        $this->assertSame('', (string) $note->dealer_other_mobile, 'sent empty, so cleared');
        $this->assertSame('Saregam', $note->dealer_other_address, 'not sent, so left alone');
    }

    /** An old build editing a note cannot wipe fields it does not know about. */
    public function testAnOldBuildEditingANoteLeavesTheNewFieldsAlone()
    {
        $id = $this->postJson('/api/executive/work-notes', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $this->postJson('/api/executive/work-notes/' . $id, [
            'subject'     => 'Edited by an old build',
            'action_date' => '2026-07-27',
            'action_time' => '11:30',
        ], $this->headers())->assertStatus(200);

        $note = WorkNote::findOrFail($id);

        $this->assertSame('Edited by an old build', $note->subject);
        $this->assertSame('Rajesh Prasad', $note->dealer_other);
        $this->assertSame('9408730419', $note->dealer_other_mobile);
        $this->assertSame('Saregam', $note->dealer_other_address);
    }

    /* ─────────────────────────── Validation ─────────────────────────── */

    public function testAnOverlongNameIsRejected()
    {
        $tooLong = $this->payload();
        $tooLong['dealer_other'] = str_repeat('a', 256);

        $this->postJson('/api/executive/work-notes', $tooLong, $this->headers())
            ->assertStatus(422);
    }

    /* ─────────────────────────── Reports ─────────────────────────── */

    /** The daily report names the dealer instead of printing a dash. */
    public function testTheDailyReportShowsTheFreeTextDealerName()
    {
        $id = $this->postJson('/api/executive/work-notes', $this->payload(), $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $note    = WorkNote::with(['dealer', 'customer', 'customerRegisterRequest'])->findOrFail($id);
        $command = new \App\Console\Commands\SendDailyWorkReport();

        $method = new \ReflectionMethod($command, 'buildWorkNoteRow');
        $method->setAccessible(true);
        $row = $method->invoke($command, $note);

        $this->assertSame('Rajesh Prasad', $row['name'],
            'a dealer who is not on the system still has a name to print');
        $this->assertTrue($row['is_other']);
        $this->assertSame('9408730419', $row['other_mobile'], 'the mobile is printed too');
        $this->assertSame('Saregam', $row['other_address'], 'and the address');
    }

    /** A party that IS on the system carries no "other" details. */
    public function testAWorkNoteOnARegisteredDealerCarriesNoOtherDetails()
    {
        $dealer = \App\Dealer::where('status', 1)->firstOrFail();

        $payload = $this->payload();
        $payload['dealer_id'] = $dealer->id;

        $id = $this->postJson('/api/executive/work-notes', $payload, $this->headers())
            ->assertStatus(200)
            ->json('data.work_note.id');

        $note    = WorkNote::with(['dealer', 'customer', 'customerRegisterRequest'])->findOrFail($id);
        $command = new \App\Console\Commands\SendDailyWorkReport();

        $method = new \ReflectionMethod($command, 'buildWorkNoteRow');
        $method->setAccessible(true);
        $row = $method->invoke($command, $note);

        $this->assertSame($dealer->business_name, $row['name'], 'the registered dealer wins');
        $this->assertFalse($row['is_other']);
        $this->assertNull($row['other_mobile'], 'nothing extra is printed for a dealer on the system');
        $this->assertNull($row['other_address']);
    }

    /** The generated PDF actually prints all three. */
    public function testTheReportPdfPrintsTheNameMobileAndAddress()
    {
        if (!trim((string) shell_exec('which pdftotext'))) {
            $this->markTestSkipped('pdftotext is needed to read the generated PDF back.');
        }

        $user = $this->executive;
        $date = \Carbon\Carbon::parse($this->payload()['date']);

        WorkNote::create(['user_id' => $user->id] + $this->payload());

        $command = new \App\Console\Commands\SendDailyWorkReport();

        $gather = new \ReflectionMethod($command, 'gatherReportData');
        $gather->setAccessible(true);
        $data = $gather->invoke($command, $user, $date, $date->copy()->addDay());

        $generate = new \ReflectionMethod($command, 'generatePdf');
        $generate->setAccessible(true);
        $path = $generate->invoke($command, $user, $data, $date);

        $text = (string) shell_exec('pdftotext -layout ' . escapeshellarg($path) . ' - 2>/dev/null');
        @unlink($path);

        $this->assertContains('Rajesh Prasad', $text);
        $this->assertContains('9408730419', $text, 'the mobile is on the report');
        $this->assertContains('Saregam', $text, 'and the address');
    }
}
