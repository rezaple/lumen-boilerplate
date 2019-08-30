<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Ramsey\Uuid\Uuid;
use App\Jobs\SendVerifyMail;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
// use Illuminate\Support\Facades\DB;
use App\Jobs\SendSuccessVerifyMail;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResponseTrait;
// use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class RegisterController extends Controller
{
	use ResponseTrait{
        ResponseTrait::__construct as responseTraitConstruct;
    }

	private $client;

	public function __construct($value='')
	{
		$this->responseTraitConstruct();
		$this->client = Client::find(2);
	}

	/**
	 * banyak pr nya
	 * email pake queue biar gk berat
	 * apakah ada email activation? atau admin activation?
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function register(Request $request)
	{
		 // Validation
        $validatorResponse = $this->validateRequest($request, $this->storeRequestValidationRules($request));

        // Send failed response if validation fails
        if ($validatorResponse !== true) {
            return $this->respondBadRequest('Bad Request',$validatorResponse);
        }

        $confirmation_code = str_random(30);

		$user = User::create([
			'username' => "",
            'email' => strtolower($request['email']),
            'uid'=>Uuid::uuid4()->toString(),
            'is_active'=>false,
			'password' => app('hash')->make($request['password']),
            'confirmation_code' => $confirmation_code
		]);

        $url= 'https://pekerjakita.id/verify/'.$user->confirmation_code;

		$verify = array_merge(config('mail.verify'), ['url' =>$url,'name'=>"New User"]);

        //dispatch job
        //Mail::to($user->email)->send(new VerifyMail($verify));
        dispatch(new SendVerifyMail($user->email, $verify));

		return $this->setStatusCode(201)
            ->respond([
            	'status'=>201,
                'message' => 'Registration success.'
            ]);
	}

	public function confirm($confirmation_code)
    {
        if( ! $confirmation_code)
        {
            return $this->setStatusCode(400)
            ->respond([
            	'status'=>400,
                'message' => 'Invalid Email Verification Token!'
            ]);
        }

        $user = User::whereConfirmationCode($confirmation_code)->first();

        if ( ! $user)
        {
            return $this->setStatusCode(400)
            ->respond([
            	'status'=>400,
                'message' => 'Invalid Email Verification Token!'
            ]);
        }

        $user->confirmed = 1;
        $user->is_active = 1;
        $user->confirmation_code = null;
        $user->save();

        $dataPromo =  \App\Models\Promo::where('is_active',true)->where('is_for_register',true)->first();
        if($dataPromo){
            $data= $dataPromo->toArray();
        }else{
            $data=null;
        }

        dispatch(new SendSuccessVerifyMail($user->email, ['name'=>$user->username, 'promo' =>$data]));

        return $this->setStatusCode(200)
            ->respond([
            	'status'=>200,
                'message' => 'You have successfully verified your account.'
            ]);
    }

    private function storeRequestValidationRules(Request $request)
    {
        $rules = [
			'email'	=> 'required|email|unique:users,email',
			'password'=>'required|min:6|confirmed'
        ];
        return $rules;
    }

}
