<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public $lead;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = $this->subject($this->lead->catalog->subject)
            ->view('mail.lead')
            ->with([
                'lead' => $this->lead
            ]);

        return $message;
    }
}
