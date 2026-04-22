<?php

namespace App\Mail;

use App\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $template;

    public function __construct(EmailTemplate $template, $mailData = [])
    {
        $this->template = $template;
        $this->mailData = $mailData;
    }

    public function build()
    {
        $mail = $this->subject($this->parsePlaceholders($this->template->subject))
                     ->view($this->template->blade_view)
                     ->with($this->mailData);

        $cc = array_values(array_filter((array) ($this->template->cc_emails ?? [])));
        if (!empty($cc)) {
            $mail->cc($cc);
        }

        $bcc = array_values(array_filter((array) ($this->template->bcc_emails ?? [])));
        if (!empty($bcc)) {
            $mail->bcc($bcc);
        }

        return $mail;
    }

    protected function parsePlaceholders($subject)
    {
        return preg_replace_callback('/\{([\w.]+)\}/', function ($matches) {
            $key = $matches[1];

            // ── Special computed placeholders ──────────────────────────
            $special = $this->resolveSpecialPlaceholder($key);
            if ($special !== null) {
                return $special;
            }

            // ── Generic dot notation fallback ──────────────────────────
            $value = $this->resolveDotNotation($key, $this->mailData);
            return $value !== null ? $value : $matches[0];
        }, $subject);
    }

    /**
     * Handle special placeholder keys with custom logic.
     * Returns null if not a special key (falls through to dot notation).
     */
    protected function resolveSpecialPlaceholder($key)
    {
        // ── dealer.business_name ───────────────────────────────────────
        // Priority: short_name → first word of business_name
        if ($key === 'dealer.business_name') {
            $dealer = isset($this->mailData['po'])
                ? $this->mailData['po']->dealer
                : (isset($this->mailData['dealer']) ? $this->mailData['dealer'] : null);

            if (!$dealer) {
                return null;
            }

            // 1. short_name exists and is not empty — use it
            if (!empty($dealer->short_name)) {
                return trim($dealer->short_name);
            }

            // 2. First word of business_name
            if (!empty($dealer->business_name)) {
                return explode(' ', trim($dealer->business_name))[0];
            }

            // 3. Fallback to name if business_name also missing
            if (!empty($dealer->name)) {
                return explode(' ', trim($dealer->name))[0];
            }

            return null;
        }

        // ── dealer.city ────────────────────────────────────────────────
        // First 3 letters uppercase — e.g. Ludhiana → LUD
        if ($key === 'dealer.city') {
            $dealer = isset($this->mailData['po'])
                ? $this->mailData['po']->dealer
                : (isset($this->mailData['dealer']) ? $this->mailData['dealer'] : null);

            if (!$dealer || empty($dealer->city)) {
                return null;
            }

            return strtoupper(substr(trim($dealer->city), 0, 3));
        }

        return null; // not a special key
    }

    protected function resolveDotNotation($key, $data)
    {
        $parts = explode('.', $key);
        $value = $data;

        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } elseif (is_object($value) && isset($value->{$part})) {
                $value = $value->{$part};
            } else {
                return null;
            }
        }

        return is_scalar($value) ? $value : null;
    }
}