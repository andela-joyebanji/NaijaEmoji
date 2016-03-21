<?php

namespace Pyjac\NaijaEmoji\Controller;

use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Helpers;
use Pyjac\NaijaEmoji\Model\BlacklistedToken;
use Pyjac\NaijaEmoji\Model\User;
use Pyjac\NaijaEmoji\Security\UserAuthenticator;
use Firebase\JWT\JWT;

final class AuthController
{
	public function login($request, $response, $args) {
        $userData = $request->getParsedBody();
        if(!$userData || !Helpers::keysExistAndNotEmptyString(["username","password"], $userData)){

             return $response->withJson(["message" => "Username or Password field not provided."],400);
        }
        
        $user = UserAuthenticator::authenticate($userData["username"],$userData["password"]);
         
        if( !$user ){
            return $response->withJson(["message" => "Username or Password not valid."],401);
        }

        return $response->withJson(["token" => $this->generateToken($user->id)]);
    }

    private function generateToken($userId){
    	$appSecret = getenv('APP_SECRET');
        $jwtAlgorithm = getenv('JWT_ALGORITHM');
        $timeIssued = time();
        $tokenId    = base64_encode(mcrypt_create_iv(32));
        $token = [
            "iat" => $timeIssued,   // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            "nbf" => $timeIssued + 5, //5 seconds
            "exp" => $timeIssued + 60 * 60 * 24 * 30, // expires in 30 days
            'data' => [                  // Data related to the signer user
                'userId'   => $userId // userid from the users table
            ]
        ];

        return JWT::encode($token, $appSecret, $jwtAlgorithm);
    }

    public function register($request, $response, $args) {
        $userData = $request->getParsedBody();
        
        if(!$userData || !Helpers::keysExistAndNotEmptyString(["username","password"], $userData)){

             return $response->withJson(["message" => "Username or Password field not provided."],400);
        }

        $username =$userData["username"];
        $password = $userData["password"];
        if(User::where("username", $username)->first()){
            return $response->withJson(["message" => "Username already exist."],409);
        }

        $user = new User();
        $user->username = $username;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->role = "member";
        $user->save();

        return $response->withJson(["message" => "User successfully created."], 201);
    }
}