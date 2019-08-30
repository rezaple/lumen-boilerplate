<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $url;
    protected $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url,$name)
    {
        $this->url= $url;

        $this->name= $name;
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
                ->subject('Reset Password')->markdown('emails.forgot-password')->with(['link'=>$this->url,'name'=>$this->name]);
    }
}
