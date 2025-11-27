<?php

namespace App\Mail;

use App\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerPOCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $po;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
    }

    public function build()
    {
        return $this->subject('Your Purchase Order is Confirmed - ' . $this->po->po_ref_no_string)
                    ->markdown('emails.purchase_order.customer');
    }
}
