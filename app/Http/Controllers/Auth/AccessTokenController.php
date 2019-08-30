<?php

namespace App\Http\Controllers\Auth;

use Laravel\Passport\Passport;
use Laravel\Passport\Token;

/**
 * Class AccessTokenController
 * @package Dusterio\LumenPassport\Http\Controllers
 */
class AccessTokenController extends \Laravel\Passport\Http\Controllers\AccessTokenController
{

    public function createAccessToken(Request $request)
    {
        $inputs = $request->all();

        //Set default scope with full access
        if (!isset($inputs['scope']) || empty($inputs['scope'])) {
            $inputs['scope'] = "*";
        }

        $tokenRequest = $request->create('/oauth/token', 'post', $inputs);

        // forward the request to the oauth token request endpoint
        return app()->dispatch($tokenRequest);
    }

    /**
     * Revoke the user's other access tokens for the client.
     *
     * @param  Token $token
     * @param  string $tokenId
     * @return void
     */
    protected function revokeOrDeleteAccessTokens(Token $token, $tokenId)
    {
        $query = Token::where('user_id', $token->user_id)->where('client_id', $token->client_id);

        if ($tokenId) {
            $query->where('id', '<>', $tokenId);
        }

        if (Passport::$pruneRevokedTokens) {
            $query->delete();
        } else {
            $query->update(['revoked' => true]);
        }
    }
}
