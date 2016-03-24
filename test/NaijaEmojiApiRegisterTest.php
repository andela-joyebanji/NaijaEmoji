<?php

use Slim\Http\Environment;
use Slim\Http\Request;

require_once 'NaijaEmojiApiTest.php';

class NaijaEmojiApiRegisterTest extends NaijaEmojiApiTest
{
	 public function testRegisterReturnsStatusCode400WithMsgWhenOnlyUsernameIsPassed()
    {
        $response = $this->post('/auth/register', ['username' => 'tester']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $this->registerErrorMessage);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testRegisterReturnsStatusCode400WithMsgWhenOnlyPasswordIsPassed()
    {
        $response = $this->post('/auth/register', ['password' => 'tester']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $this->registerErrorMessage);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testRegisterReturnsStatusCode400WithMsgWhenUsernameAndPasswordIsNotPassed()
    {
        $response = $this->post('/auth/register', []);
        $result = json_decode($response->getBody(), true);

        $this->assertEquals($result['message'], $this->registerErrorMessage);
        $this->assertSame($response->getStatusCode(), 400);

    }

    public function testRegisterReturnsStatusCode400WithMsgWhenOnlyUsernameIsPassedWithEmptyString()
    {
        $response = $this->post('/auth/register', ['username' => ' ']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $this->registerErrorMessage);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testRegisterReturnsStatusCode400WithMsgWhenOnlyPasswordIsPassedWithEmptyString()
    {
        $response = $this->post('/auth/register', ['password' => ' ']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $this->registerErrorMessage);
        $this->assertSame($response->getStatusCode(), 400);
    }


    public function testRegisterReturnsStatusCode400WithMsgWhenUsernameAndPasswordIsPassedWithEmptyStrings()
    {
        $response = $this->post('/auth/register', ['username' => ' ','password' => ' ']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $this->registerErrorMessage);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testRegisterReturnsStatusCode409WithMsgWhenAlreadyExistingUsernameWithPasswordIsPassed()
    {

        $response = $this->post('/auth/register',['username' => 'tester','password' => 'pyjac']);

        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Username already exist.');
        $this->assertSame($response->getStatusCode(), 409);
    }

    public function testRegisterReturnsStatusCode201WithMsgWhenUniqueUsernameAndPasswordIsPassed()
    {

        $response = $this->post('/auth/register',['username' => 'pyjac','password' => 'pyjac']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'User successfully created.');
        $this->assertSame($response->getStatusCode(), 201);
    }

}