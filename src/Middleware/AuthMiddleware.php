<?php

namespace Pyjac\NaijaEmoji\Middleware;

use Firebase\JWT\JWT;
use Pyjac\NaijaEmoji\Model\User;

class AuthMiddleware
{
    /**
     * Middleware invokable class method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (!$request->hasHeader('authorization')) {
            throw new \UnexpectedValueException('Token not provided');
        }

        $userJwt = $this->getUserToken($request);
        $jwtToken = JWT::decode($userJwt, getenv('APP_SECRET'), [getenv('JWT_ALGORITHM')]);
        $user = User::with('blacklistedTokens')->where('id', $jwtToken->data->userId)->first();

        if ($user->blacklistedTokens()->where('token_jti', $jwtToken->jti)->get()->first()) {
            throw new \DomainException('Your token has been logged out.');
        }
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('token_jti', $jwtToken->jti);

        return $next($request, $response);
    }

    /**
     * Get user token from request header.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    public function getUserToken($request)
    {
        // Get the authorization header value in other to retrieve the token
        $authHeader = $request->getHeader('authorization');
        list($userJwt) = sscanf($authHeader[0], 'Bearer %s');
        if (!$userJwt) {
            throw new \UnexpectedValueException('Token not provided');
        }

        return $userJwt;
    }
}
