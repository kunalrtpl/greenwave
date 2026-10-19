<?php

namespace Tests\Feature;

use App\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Every email writes one line to the Laravel log saying who it reached.
 *
 * That line is how a test run is checked without anyone opening an inbox, so
 * it has to be complete: TO, CC and BCC — including the CC/BCC that live on
 * the template row itself and are added inside DynamicMail rather than by the
 * mailer, which the log used to miss entirely.
 */
class EmailLoggingTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\EmailTemplate */
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = EmailTemplate::where('is_active', 1)->firstOrFail();
        // The columns are cast to array, so they are set with arrays —
        // handing them JSON would store it encoded twice.
        $this->template->update([
            'to_emails'  => ['default@example.com'],
            'cc_emails'  => ['template-cc@example.com'],
            'bcc_emails' => ['template-bcc@example.com'],
        ]);
        $this->template = $this->template->fresh();

        Mail::fake();
    }

    /**
     * Capture what EmailService logged.
     *
     * @return array [message, context] of the "Sent" line
     */
    private function capturedSendLog(callable $send)
    {
        $lines = [];

        Log::listen(function ($log) use (&$lines) {
            // 5.8 passes a MessageLogged event; older signatures pass three args.
            $lines[] = is_object($log)
                ? ['level' => $log->level, 'message' => $log->message, 'context' => $log->context]
                : ['level' => null, 'message' => (string) $log, 'context' => []];
        });

        $send();

        foreach ($lines as $line) {
            if (strpos($line['message'], 'EmailService: Sent') === 0) {
                return $line;
            }
        }

        return null;
    }

    /** The log line names everyone: TO, the passed CC, and the template's BCC. */
    public function testTheLogNamesEveryoneTheEmailReached()
    {
        config(['mail.redirect_to' => null]);

        $line = $this->capturedSendLog(function () {
            EmailService::sendTemplate(
                $this->template,
                ['po' => tap(new \App\PurchaseOrder(), function ($po) { $po->id = 1203; })],
                ['dealer@example.com'],
                ['linked-employee@example.com']
            );
        });

        $this->assertNotNull($line, 'a send writes one "Sent" line');

        $this->assertSame(['dealer@example.com'], $line['context']['to']);
        $this->assertContains('linked-employee@example.com', $line['context']['cc'],
            'the CC worked out at the call site');
        $this->assertContains('template-cc@example.com', $line['context']['cc'],
            "and the template's own CC, which the mailable adds");
        $this->assertSame(['template-bcc@example.com'], $line['context']['bcc'],
            'the BCC too — it used to be invisible in the log');
        $this->assertFalse($line['context']['redirected']);
    }

    /** It says which record the email was about, so a line can be traced back. */
    public function testTheLogSaysWhatTheEmailWasAbout()
    {
        $po = new \App\PurchaseOrder();
        $po->id = 1203;

        $line = $this->capturedSendLog(function () use ($po) {
            EmailService::sendTemplate($this->template, ['po' => $po], ['dealer@example.com']);
        });

        $this->assertSame('PurchaseOrder#1203', $line['context']['about']['po']);
    }

    /**
     * While redirecting, the log says so and the template's own CC/BCC are
     * dropped — otherwise a real dealer in cc_emails would be written to
     * during a test run however the TO was redirected.
     */
    public function testRedirectingDropsTheTemplatesOwnCcAndBcc()
    {
        config(['mail.redirect_to' => 'tester@example.com']);

        $line = $this->capturedSendLog(function () {
            EmailService::sendTemplate($this->template, [], ['dealer@example.com'], ['linked@example.com']);
        });

        $this->assertSame(['tester@example.com'], $line['context']['to']);
        $this->assertSame([], $line['context']['cc'], 'nothing is CC\'d while redirecting');
        $this->assertSame([], $line['context']['bcc'], 'and nothing is BCC\'d');
        $this->assertTrue($line['context']['redirected']);

        Mail::assertSent(\App\Mail\DynamicMail::class, function ($mail) {
            $mail->build();
            return $mail->bcc === [] || empty($mail->bcc);
        });

        // The template row itself is untouched — only the copy that was sent.
        $this->assertSame(['template-bcc@example.com'], $this->template->fresh()->bcc_emails);
    }

    /** A send with nobody to write to says so instead of failing quietly. */
    public function testNoRecipientsIsLoggedAsAWarning()
    {
        $this->template->update(['to_emails' => []]);

        $warned = false;
        Log::listen(function ($log) use (&$warned) {
            if (is_object($log) && strpos($log->message, 'No recipients') !== false) {
                $warned = true;
            }
        });

        EmailService::sendTemplate($this->template->fresh(), []);

        $this->assertTrue($warned);
        Mail::assertNothingSent();
    }
}
