<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GasFeeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $amount;
    public $subject;
    public $isAdmin;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $amount, $subject, $isAdmin = false)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->subject = $subject;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.gasfee_notification');
    }
}
