<?php

namespace App\Mail;

use App\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPOCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $po;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
    }

    public function build()
    {
        return $this->subject('New Purchase Order Created - ' . $this->po->po_ref_no_string)
                    ->markdown('emails.purchase_order.admin');
    }
}
