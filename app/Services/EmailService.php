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
     * @param array             $mailData   Data passed to blade view
     * @param string|array|null $to         If null, uses to_emails from DB
     */
    public static function send($eventKey, $mailData = [], $to = null)
    {
        try {
            $template = EmailTemplate::getActive($eventKey);

            if (!$template) {
                Log::warning("EmailService: No active template for [{$eventKey}]. Skipping.");
                return;
            }

            $recipients = self::resolveRecipients($template, $to);

            if (empty($recipients)) {
                Log::warning("EmailService: No recipients for [{$eventKey}]. Skipping.");
                return;
            }

            Mail::to($recipients)->send(new DynamicMail($template, $mailData));

            Log::info("EmailService: Sent [{$eventKey}]", ['recipients' => $recipients]);

        } catch (\Exception $e) {
            Log::error("EmailService: Failed [{$eventKey}] — " . $e->getMessage());
        }
    }

    protected static function resolveRecipients($template, $to)
    {
        if (!empty($to)) {
            return array_values(array_filter((array) $to));
        }

        return array_values(array_filter((array) ($template->to_emails ?? [])));
    }
}