<?php

namespace App\Http\Controllers\Auth;

use DB;
use App\Models\User;
use App\Jobs\SendResetMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\PasswordBroker;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Http\Controllers\Auth\Traits\ResetPasswords;

class ResetPasswordController extends Controller
{
    use ResetPasswords;
     use ResponseTrait{
        ResponseTrait::__construct as responseTraitConstruct;
    }

    public function __construct()
    {
        $this->responseTraitConstruct();
        $this->broker = 'users';
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        if( $user = User::where('email', $request->input('email') )->first() )
        {
            $hasher = app()->make('hash');
            $token =str_random(60);

            DB::table('password_resets')->where('email',$user->email)->delete();
            DB::table('password_resets')->insert([
                'email' => $user->email,
                'token' => $hasher->make($token)
            ]);

            $url= 'https://kliktrip.id/resetpassword/'.$token;

            dispatch(new SendResetMail($user->email, $url, $user->username));

            return $this->setStatusCode(200)
                ->respond([
                    'status'=>200,
                    'message' => 'Success send link reset password'
                ]);
        }

        return $this->setStatusCode(400)
                    ->respond([
                        'status'=>400,
                        'message' => 'Invalid user'
                    ]);
    }

    protected function resetPassword($user, $password)
    {
        $hasher = app()->make('hash');
        $user->password = $hasher->make($password);
        $user->save();
        return $this->setStatusCode(200)
                ->respond([
                    'status'=>200,
                    'message' => 'Success reset password'
                ]);
    }
}
