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
     */
    public static function send(string $eventKey, array $mailData = [], $to = null): void
    {
        try {
            $template = EmailTemplate::getActive($eventKey);
            if (!$template) {
                Log::warning("EmailService: No active template for [{$eventKey}]. Skipping.");
                return;
            }
            self::sendTemplate($template, $mailData, $to);
        } catch (\Exception $e) {
            Log::error("EmailService: Failed [{$eventKey}] — " . $e->getMessage());
        }
    }

    /**
     * Send an email using an already-resolved EmailTemplate model.
     *
     * @param EmailTemplate     $template
     * @param array             $mailData
     * @param string|array|null $to         Override recipients; null = use to_emails from DB
     */
    public static function sendTemplate(EmailTemplate $template, array $mailData = [], $to = null): void
    {
        try {
            $recipients = self::resolveRecipients($template, $to);
            if (empty($recipients)) {
                Log::warning("EmailService: No recipients for template [{$template->event_key}] (id={$template->id}). Skipping.");
                return;
            }

            Mail::to($recipients)->send(new DynamicMail($template, $mailData));

            Log::info("EmailService: Sent template [{$template->event_key}] (id={$template->id})", [
                'recipients' => $recipients,
            ]);
        } catch (\Exception $e) {
            Log::error(
                "EmailService: Failed template [{$template->event_key}] (id={$template->id}) — " . $e->getMessage()
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internals
    // ─────────────────────────────────────────────────────────────────────────

    protected static function resolveRecipients(EmailTemplate $template, $to): array
    {
        if (!empty($to)) {
            return array_values(array_filter((array) $to));
        }
        return array_values(array_filter((array) ($template->to_emails ?? [])));
    }
}