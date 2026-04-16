<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendConfirmPaymentOrder extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $settings;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, ?Setting $settings)
    {
        $this->order = $order;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Seu pagamento foi confirmado!")
            ->from(config('mail.from.address'), $this->order->store->people->name)
            ->markdown('mails.payment-confirm');
    }
}
