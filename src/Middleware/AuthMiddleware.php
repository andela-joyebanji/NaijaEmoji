<?php

namespace Pyjac\NaijaEmoji\Middleware;

use Firebase\JWT\JWT;

class AuthMiddleware
{
    /**
     * middleware invokable class.
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
        // Get the authorization header value in other to retrieve the token
        $authHeader = $request->getHeader('authorization');
        list($userJwt) = sscanf($authHeader[0], 'Bearer %s');
        if (!$userJwt) {
            throw new \UnexpectedValueException('Token not provided');
        }
        $jwtToken = JWT::decode($userJwt, getenv('APP_SECRET'), [getenv('JWT_ALGORITHM')]);
        $user = \Pyjac\NaijaEmoji\Model\User::with('blacklistedTokens')->where('id', $jwtToken->data->userId)->first();

        if ($user->blacklistedTokens()->where('token_jti', $jwtToken->jti)->get()->first()) {
            throw new \DomainException("Your token has been logged out.");
        }
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('token_jti', $jwtToken->jti);

        return $next($request, $response);
    }
}
