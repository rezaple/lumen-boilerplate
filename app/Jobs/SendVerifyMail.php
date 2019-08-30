<?php

namespace App\Jobs;

use App\Mail\VerifyMail;
use Illuminate\Support\Facades\Mail;

class SendVerifyMail extends Job
{

    protected $email;

    protected $verify;

    public function __construct($email,$verify)
    {
        $this->queue = 'email';
        $this->email = $email;
        $this->verify = $verify;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new VerifyMail($this->verify));
    }
}
