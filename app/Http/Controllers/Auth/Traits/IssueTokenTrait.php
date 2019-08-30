<?php

namespace App\Http\Controllers\Auth\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait IssueTokenTrait
{
	public function issueToken(Request $request, $grantType, $scope="")
	{
		$params=[
			'grant_type'	=> $grantType,
			'client_id'		=> $this->client->id,
			'client_secret' => $this->client->secret,
			'scope'			=> $scope
		];

		if($grantType !== 'social'){
			$params['username']= $request->username ?: $request->email;
		}

		if($grantType == 'social'){
			$params['provider']= $request->provider;
			$params['provider_user_id']= $request->provider_user_id;
		}

		$proxy = Request::create('oauth/token','POST', $params);

		return App::dispatch($proxy);
	}
}
