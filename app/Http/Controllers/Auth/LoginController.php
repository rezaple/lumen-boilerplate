<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Models\FirebaseLog;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use App\Models\User;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ResponseTrait;

class LoginController extends Controller
{
	 use ResponseTrait{
        ResponseTrait::__construct as responseTraitConstruct;
    }

	private $client;

	public function __construct()
	{
		$this->responseTraitConstruct();
		$this->client = Client::where('password_client',true)->first();
	}

	public function login(Request $request)
	{
		 // Validation
        $validatorResponse = $this->validateRequest($request, $this->storeRequestValidationRules($request));

        // Send failed response if validation fails
        if ($validatorResponse !== true) {
            return $this->respondBadRequest('Bad Request',$validatorResponse);
        }
		$hasher = app()->make('hash');
		$email =strtolower($request->email);
		$user = User::where('email', $email)->first();

        if (!$user) {
            return $this->setStatusCode(401)
            		->respond([
            			'status'=>401,
                		'message' => 'The user credentials were incorrect.'
            		]);
        }else{
            if (!$hasher->check($request->password, $user->password)) {
                return $this->setStatusCode(401)
            		->respond([
            			'status'=>401,
                		'message' => 'The user credentials were incorrect.'
            	]);
            }
		}

		$params=[
			'grant_type'	=> 'password',
			'client_id'		=> $this->client->id,
			'client_secret' => $this->client->secret,
			'username'		=> $email,
			'password'		=> $request['password'],
			'scope'			=> strtolower($user->role),
		];
		$proxy = Request::create('/oauth/token','POST', $params);

		return App::dispatch($proxy);
	}

	public function refresh(Request $request)
	{
		$this->validate($request, [
			'refresh_token' =>'required'
		]);

		$params=[
			'grant_type'	=> 'refresh_token',
			'client_id'		=> $this->client->id,
			'client_secret' => $this->client->secret,
			'refresh_token' => $request['refresh_token'],
			'scope'=>''
		];

		$proxy = Request::create('/oauth/token','POST', $params);

		return App::dispatch($proxy);
	}

	public function logout(Request $request)
	{
		$user=Auth::user();
		$accessToken = $user->token();
		$user->save();
		$refreshToken= \DB::table('oauth_refresh_tokens')
						->where('access_token_id', $accessToken->id)
						->update(['revoked' => true]);

		$accessToken->revoke();

		return response()->json([], 204);

	}

	private function storeRequestValidationRules(Request $request)
    {
        $rules = [
            'email'	=> 'required|email',
			'password'=>'required'
        ];
        return $rules;
    }
}
