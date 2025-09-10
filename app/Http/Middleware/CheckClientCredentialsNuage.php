<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Http\Middleware\CheckCredentials;
use Laravel\Passport\Token;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

class CheckClientCredentialsNuage extends CheckCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     *
     * @throws \Laravel\Passport\Exceptions\AuthenticationException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $psr = (new PsrHttpFactory(
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory
        ))->createRequest($request);

        try {
            $psr = $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            //throw new AuthenticationException;
            return response()->json(['message' => "You are not allowed to access this site. [error 203]"]);
        }

        $this->validate($psr, $scopes);

        return $next($request);
    }

    /**
     * @param $psr
     * @param $scopes
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthenticationException
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validate($psr, $scopes)
    {
        parent::validate($psr, $scopes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("LIVE")) {
            //Retrive the Token ID
            $accessTokenID = $psr->getAttribute('oauth_access_token_id');

            //retrieve the access token
            $accessToken = Token::find($accessTokenID);

            if(!$accessToken) {
                return response()->json(['message' => "You are not allowed to access this site. [error 203]"]);
            }

            //Retrieve the client ID associated with the access token
            $clientId = $accessToken->client_id;

            //Retrive the client using the client ID
            $client = Client::find($clientId);

            if(!$client || !$client->is_live) {
                return response()->json(['message' => "You are not allowed to access this site. [error 203]"]);
            }
        }
    }

    protected function validateCredentials($token)
    {
        if (! $token) {
            return response()->json(['message' => "You are not allowed to access this site. [error 204]"]);
        }
    }

    protected function validateScopes($token, $scopes)
    {
        // TODO: Implement validateScopes() method.
    }
}
