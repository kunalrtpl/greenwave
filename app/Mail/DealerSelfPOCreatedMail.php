<?php

namespace App\Mail;

use App\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DealerSelfPOCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $po;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
    }

    public function build()
    {
        return $this->subject('Your Purchase Order Has Been Created - ' . $this->po->po_ref_no_string)
                    ->markdown('emails.purchase_order.dealer_self');
    }
}
