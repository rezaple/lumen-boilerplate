<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Mail\Markdown;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResponseTrait;

class MailController extends Controller
{
    use ResponseTrait{
        ResponseTrait::__construct as responseTraitConstruct;
    }

    protected $user;

    public function __construct()
    {
        $this->responseTraitConstruct();
        $this->user= Auth::user();
    }

    public function testSendMailRegister()
    {
        $dataOrder =['name'=>'test','url'=>'asdasd'];
        $markdown = new Markdown(view(), config('mail.markdown'));
        //return $dataOrder;
        return $markdown->render('emails.verify', $dataOrder);
    }

    public function testSendMailForgot()
    {
        $dataOrder =['name'=>'test','link'=>'asdasd'];
        $markdown = new Markdown(view(), config('mail.markdown'));
        //return $dataOrder;
        return $markdown->render('emails.forgot-password', $dataOrder);
    }
}
