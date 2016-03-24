<?php

use Slim\Http\Environment;
use Slim\Http\Request;

require_once 'NaijaEmojiApiTest.php';

class NaijaEmojiApiLogoutTest extends NaijaEmojiApiTest
{
	public function testLogoutReturnsStatusCode400WithMsgWhenAuthorizationHeaderIsSetButTokenIsNotPresent()
    {
        $env = Environment::mock([
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => '/auth/logout',
            'HTTP_AUTHORIZATION' => '',
            'CONTENT_TYPE'       => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Token not provided');
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testLogoutReturnsStatusCode400WithMsgWhenInvalidTokenIsPassed()
    {

        $response = $this->postWithToken('/auth/logout',"lblkbbbvvgjh", []);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Wrong number of segments');
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testLogoutReturnsStatusCode200WithMsgWhenValidTokenIsPassed()
    {
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/auth/logout',$token, []);
        $result = (string)$response->getBody();
        $this->assertContains('Logout Successful',$result);
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testLogoutReturnsStatusCode400WithMsgWhenAuthorizationHeaderIsNotSet()
    {

        $response = $this->post('/auth/logout',[]);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Token not provided');
        $this->assertSame($response->getStatusCode(), 400);
    }

}
