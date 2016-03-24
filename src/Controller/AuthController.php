<?php

namespace Pyjac\NaijaEmoji\Controller;

use Firebase\JWT\JWT;
use Pyjac\NaijaEmoji\Helpers;
use Pyjac\NaijaEmoji\Model\BlacklistedToken;
use Pyjac\NaijaEmoji\Model\User;
use Pyjac\NaijaEmoji\Security\UserAuthenticator;

final class AuthController
{
    /**
     * Login a user.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     *
     * @return Slim\Http\Response
     */
    public function login($request, $response)
    {
        $userData = $request->getParsedBody();
        if ($this->validateUserData($userData)) {
            return $response->withJson(['message' => 'Username or Password field not provided.'], 400);
        }

        $user = UserAuthenticator::authenticate($userData['username'], $userData['password']);

        if (!$user) {
            return $response->withJson(['message' => 'Username or Password not valid.'], 401);
        }

        return $response->withJson(['token' => $this->generateToken($user->id)]);
    }

    /**
     * Generate a token for user with passed Id.
     * @param  int $userId 
     *         
     * @return string
     */
    private function generateToken($userId)
    {
        $appSecret = getenv('APP_SECRET');
        $jwtAlgorithm = getenv('JWT_ALGORITHM');
        $timeIssued = time();
        $tokenId = base64_encode(mcrypt_create_iv(32));
        $token = [
            'iat'  => $timeIssued,   // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'nbf'  => $timeIssued, //Not before time
            'exp'  => $timeIssued + 60 * 60 * 24 * 30, // expires in 30 days
            'data' => [                  // Data related to the signer user
                'userId'   => $userId, // userid from the users table
            ],
        ];

        return JWT::encode($token, $appSecret, $jwtAlgorithm);
    }

    public function logout($request, $response)
    {
        $user = $request->getAttribute('user');
        $blacklistedToken = new BlacklistedToken();
        $blacklistedToken->token_jti = $request->getAttribute('token_jti');
        $user->blacklistedTokens()->save($blacklistedToken);

        return $response->withJson(['message' => 'Logout Successful'], 200);
    }

    /**
     * Register a user.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     *
     * @return Slim\Http\Response
     */
    public function register($request, $response)
    {
        $userData = $request->getParsedBody();

        if ($this->validateUserData($userData)) {
            return $response->withJson(['message' => 'Username or Password field not provided.'], 400);
        }

        if (User::where('username', $userData['username'])->first()) {
            return $response->withJson(['message' => 'Username already exist.'], 409);
        }

        User::firstOrCreate(
                [
                    'username' => $userData['username'], 
                    'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
                    'role'     => 'member'
                ]);
       
        return $response->withJson(['message' => 'User successfully created.'], 201);
    }

    /**
     * Validate user data are correct.
     *     
     * @param  array $userData
     * 
     * @return bool
     */
    private function validateUserData($userData)
    {
        return (!$userData || !Helpers::keysExistAndNotEmptyString(['username', 'password'], $userData));
    }
}
