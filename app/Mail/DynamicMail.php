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
            $key   = $matches[1];
            $value = $this->resolveDotNotation($key, $this->mailData);
            return $value !== null ? $value : $matches[0];
        }, $subject);
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