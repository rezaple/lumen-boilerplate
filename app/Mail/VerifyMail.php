<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $verify;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verifyData)
    {
        $this->verify= $verifyData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailFrom = config('mail.from.address');
        return $this->from($mailFrom)
                ->subject('Verify Email')->markdown('emails.verify')->with($this->verify);
    }
}
