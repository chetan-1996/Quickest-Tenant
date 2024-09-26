<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchasePlanMail extends Mailable
{
    use Queueable, SerializesModels;
    public $plan_purchase_details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($plan_purchase_details)
    {
        $this->plan_purchase_details = $plan_purchase_details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->plan_purchase_details['subject'])->view('app.emails.plan-purchase-mail')->with('plan_purchase_details', $this->plan_purchase_details);
    }
}
