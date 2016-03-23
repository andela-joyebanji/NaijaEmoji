<?php

use Pyjac\NaijaEmoji\App;
use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\User;
use Slim\Http\Environment;
use Slim\Http\Request;

require_once 'TestDatabasePopulator.php';

class NaijaEmojiApiTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    protected $user;

    public function setUp()
    {
        $this->app = (new App())->get();
        $this->user = TestDatabasePopulator::populate();
    }

    public function testLoginReturnsTokenWhenValidUsernameAndPasswordIsPassed()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/login',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => 'tester', 'password' => 'test']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertNotNull($result['token']);
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testLoginReturnsStatusCode401WhenCorrectUsernameWithWrongPasswordIsPassed()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/login',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => 'tester', 'password' => 'tes']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertFalse(isset($result['token']));
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testLoginReturnsStatusCode401WhenIncorrectUsernameWithPasswordIsPassed()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/login',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => '@tester', 'password' => 'tes']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertFalse(isset($result['token']));
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testGetAllEmojisReturnsOneEmoji()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis',
            'PATH_INFO'      => '/emojis',
            ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;

        $response = $this->app->run(true);

        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data[0]['name'], 'Suliat');
        $this->assertSame($data[0]['category'], 'sulia');
        $this->assertSame(count($data), 1);
    }

    public function testGetEmojiReturnsCorrectEmojiWithStatusCodeOf200()
    {
        $emoji = $this->user->emojis()->first();
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis/'.$emoji->id,
            'PATH_INFO'      => '/emojis',
            ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;

        $response = $this->app->run(true);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data['name'], $emoji->name);
        $this->assertSame($data['category'], $emoji->category->name);
    }

    public function testGetEmojiReturnsStatusCodeOf404WithMsgWhenEmojiWithPassedIdNotFound()
    {
        $emoji = $this->user->emojis()->first();
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis/10000',
            'PATH_INFO'      => '/emojis',
            ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;

        $response = $this->app->run(true);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 404);
        $this->assertSame($data['message'], 'The requested Emoji is not found.');
    }

    public function testRegisterReturnsStatusCode201WithMsgWhenUniqueUsernameAndPasswordIsPassed()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/register',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => 'pyjac', 'password' => 'pyjac']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'User successfully created.');
        $this->assertSame($response->getStatusCode(), 201);
    }

    public function testRegisterReturnsStatusCode409WithMsgWhenAlreadyExistingUsernameWithPasswordIsPassed()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/register',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => 'tester', 'password' => 'pyjac']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Username already exist.');
        $this->assertSame($response->getStatusCode(), 409);
    }

    public function testRegisterReturnsStatusCode400WithMsgWhenUsernameOrPasswordIsNotPassed()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/register',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $authError = 'Username or Password field not provided.';
        $req = Request::createFromEnvironment($env);
        //When username or password is not passed
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $authError);
        $this->assertSame($response->getStatusCode(), 400);

        //When only username passed
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => 'tester']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $authError);
        $this->assertSame($response->getStatusCode(), 400);

        //When only password passed
        $req = $req->withParsedBody(['password' => 'tester']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $authError);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testRegisterReturnsStatusCode400WithMsgWhenUsernameOrPasswordIsPassedButEmpty()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/register',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $authError = 'Username or Password field not provided.';
        $req = Request::createFromEnvironment($env);

        //When only username is passed with empty space
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => ' ']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $authError);
        $this->assertSame($response->getStatusCode(), 400);

        //When only password is passed with empty space
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['password' => ' ']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $authError);
        $this->assertSame($response->getStatusCode(), 400);

         //When both username and password are passed with empty space
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => ' ', 'password' => ' ']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], $authError);
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testLogoutReturnsStatusCode400WithMsgWhenAuthorizationHeaderIsNotSet()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/logout',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Token not provided');
        $this->assertSame($response->getStatusCode(), 400);
    }

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
        $env = Environment::mock([
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => '/auth/logout',
            'HTTP_AUTHORIZATION' => 'Bearer lblkbbbvvgjh',
            'CONTENT_TYPE'       => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'], 'Wrong number of segments');
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testLogoutReturnsStatusCode200WithMsgWhenValidTokenIsPassed()
    {
        $token = $this->getLoginTokenForTestUser();
        $token = 'Bearer '.$token;
        $env = Environment::mock([
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => '/auth/logout',
            'HTTP_AUTHORIZATION' => $token,
            'CONTENT_TYPE'       => 'application/x-www-form-urlencoded',
        ]);
        $reqs = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $reqs;
        $response = $this->app->run(true);
        $result = (string) $response->getBody();
        $this->assertContains('Logout Successful', $result);
        $this->assertSame($response->getStatusCode(), 200);
    }

    private function getLoginTokenForTestUser()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/login',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env);
        $req = $req->withParsedBody(['username' => 'tester', 'password' => 'test']);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);

        return $result['token'];
    }
}
