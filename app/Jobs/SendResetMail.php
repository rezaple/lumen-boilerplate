<?php

namespace App\Jobs;

use App\Mail\ResetMail;
use Illuminate\Support\Facades\Mail;

class SendResetMail extends Job
{

    protected $email;
    protected $url;
    protected $name;

    public function __construct($email,$url,$name)
    {
        $this->queue = 'email';
        $this->email = $email;
        $this->url = $url;
        $this->name = $name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new ResetMail($this->url,$this->name));
    }
}
