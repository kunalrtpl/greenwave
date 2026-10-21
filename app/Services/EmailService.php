<?php

namespace App\Services;

use App\EmailTemplate;
use App\Mail\DynamicMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send an email by event key.
     *
     * @param string            $eventKey
     * @param array             $mailData   Data passed to the blade view & placeholder parser.
     *                                      Pass '_attachments' => ['/abs/path/file.pdf'] inside
     *                                      mailData to attach files — no signature change needed.
     * @param string|array|null $to         Override recipients; null = use to_emails from DB
     * @param string|array|null $cc         Optional CC recipients
     * @return bool                        True when the message was handed to the mailer.
     *                                      Existing callers can keep ignoring it.
     */
    public static function send(string $eventKey, array $mailData = [], $to = null, $cc = null): bool
    {
        try {
            $template = EmailTemplate::getActive($eventKey);
            if (!$template) {
                Log::warning("EmailService: No active template for [{$eventKey}]. Skipping.");
                return false;
            }
            return self::sendTemplate($template, $mailData, $to, $cc);
        } catch (\Exception $e) {
            Log::error("EmailService: Failed [{$eventKey}] — " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an email using an already-resolved EmailTemplate model.
     *
     * @param EmailTemplate     $template
     * @param array             $mailData
     * @param string|array|null $to         Override recipients; null = use to_emails from DB
     * @param string|array|null $cc         Optional CC recipients
     * @return bool                        True when the message was handed to the mailer.
     */
    public static function sendTemplate(EmailTemplate $template, array $mailData = [], $to = null, $cc = null): bool
    {
        $recipients   = [];
        $ccRecipients = [];

        try {
            $recipients = self::resolveRecipients($template, $to);
            if (empty($recipients)) {
                Log::warning("EmailService: No recipients for template [{$template->event_key}] (id={$template->id}). Skipping.");
                return false;
            }

            $ccRecipients = self::resolveCc($template, $cc, $recipients);

            // DynamicMail adds the template's own CC and BCC on top of these,
            // so the mail it is given is the one whose lists are logged below.
            $mailTemplate = self::templateForSending($template);

            $mailable = new DynamicMail($mailTemplate, $mailData);

            $mail = Mail::to($recipients);
            if (!empty($ccRecipients)) {
                $mail->cc($ccRecipients);
            }
            $mail->send($mailable);

            // Everyone this message actually reached, in one line, so a test
            // run can be checked from the log alone:
            //   tail -f storage/logs/laravel-$(date +%F).log | grep EmailService
            Log::info("EmailService: Sent [{$template->event_key}] (id={$template->id})", [
                'to'         => $recipients,
                'cc'         => array_values(array_unique(array_merge(
                                    $ccRecipients,
                                    self::listOf($mailTemplate->cc_emails),
                                    self::listOf(isset($mailData['_cc']) ? $mailData['_cc'] : [])
                                ))),
                'bcc'        => self::listOf($mailTemplate->bcc_emails),
                // The subject the recipient saw, so the line names the record
                // in the same words the inbox does.
                'subject'    => $mailable->resolvedSubject(),
                'about'      => self::describe($mailData),
                'redirected' => !empty(config('mail.redirect_to')),
                // Kept for anyone already grepping the old key.
                'recipients' => $recipients,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error(
                "EmailService: Failed template [{$template->event_key}] (id={$template->id}) — " . $e->getMessage(),
                ['to' => $recipients, 'cc' => $ccRecipients]
            );
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internals
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The template handed to DynamicMail.
     *
     * While MAIL_REDIRECT_TO is set, the template's own CC and BCC are stripped
     * from the copy that is sent. They live on the mailable rather than on the
     * mailer, so without this a real dealer sitting in a template's cc_emails
     * would still be written to during a test run, however the TO was
     * redirected. The row in the database is not touched.
     */
    protected static function templateForSending(EmailTemplate $template): EmailTemplate
    {
        if (empty(config('mail.redirect_to'))) {
            return $template;
        }

        $copy = clone $template;

        if (self::listOf($template->cc_emails) || self::listOf($template->bcc_emails)) {
            Log::info("EmailService: Dropping the template's own CC/BCC for [{$template->event_key}] while redirecting", [
                'would_have_cc'  => self::listOf($template->cc_emails),
                'would_have_bcc' => self::listOf($template->bcc_emails),
            ]);
        }

        $copy->cc_emails  = [];
        $copy->bcc_emails = [];

        return $copy;
    }

    /** A clean list of addresses out of whatever shape the column holds. */
    protected static function listOf($value): array
    {
        return array_values(array_filter(array_map('trim', (array) ($value ?: []))));
    }

    /**
     * What the email was about, for the log: any model in the mail data as
     * "PurchaseOrder#1203" / "SamplingRequest#249", so a line in the log can be
     * traced back to the record that triggered it.
     */
    protected static function describe(array $mailData): array
    {
        $about = [];

        foreach ($mailData as $key => $value) {
            if ($key === '_attachments' || $key === '_cc') {
                continue;
            }
            if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                $about[$key] = class_basename($value) . '#' . $value->getKey();
            } elseif ($value instanceof \Illuminate\Support\Collection && $value->count()) {
                $first = $value->first();
                if ($first instanceof \Illuminate\Database\Eloquent\Model) {
                    $about[$key] = class_basename($first) . ' x' . $value->count();
                }
            }
        }

        return $about;
    }

    protected static function resolveRecipients(EmailTemplate $template, $to): array
    {
        $recipients = !empty($to)
            ? array_values(array_filter((array) $to))
            : array_values(array_filter((array) ($template->to_emails ?? [])));

        return self::applyRedirect($recipients, $template);
    }

    /**
     * CC list without blanks or anyone already in TO. While MAIL_REDIRECT_TO is
     * set, CC is dropped (and logged) so test mail only reaches the redirect inbox.
     */
    protected static function resolveCc(EmailTemplate $template, $cc, array $recipients): array
    {
        $requestedCc  = array_values(array_unique(array_filter((array) $cc)));
        $ccRecipients = array_values(array_diff($requestedCc, $recipients));

        $alreadyInTo = array_values(array_intersect($requestedCc, $recipients));
        if (!empty($alreadyInTo)) {
            Log::info("EmailService: CC skipped for [{$template->event_key}] — already in TO", [
                'skipped_cc' => $alreadyInTo,
            ]);
        }

        if (!empty($ccRecipients) && !empty(config('mail.redirect_to'))) {
            Log::info("EmailService: Dropping CC for [{$template->event_key}] while redirecting", [
                'would_have_cc' => $ccRecipients,
            ]);
            return [];
        }

        return $ccRecipients;
    }

    /**
     * While MAIL_REDIRECT_TO is set, every message goes to that one inbox. The
     * real recipients are still worked out and logged, so testing shows who
     * would have been written to without anyone actually being written to.
     */
    protected static function applyRedirect(array $recipients, EmailTemplate $template): array
    {
        $redirectTo = config('mail.redirect_to');

        if (empty($redirectTo) || empty($recipients)) {
            return $recipients;
        }

        Log::info("EmailService: Redirecting [{$template->event_key}] to {$redirectTo}", [
            'would_have_gone_to' => $recipients,
        ]);

        return array_values(array_filter((array) $redirectTo));
    }
}