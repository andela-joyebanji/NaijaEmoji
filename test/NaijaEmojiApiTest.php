<?php

use Pyjac\NaijaEmoji\App;
use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\Keyword;
use Pyjac\NaijaEmoji\Model\User;
use Slim\Http\Environment;
use Slim\Http\Request;
require_once 'TestDatabasePopulator.php';

class NaijaEmojiApiTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    protected $user;
    protected $registerErrorMessage;

    public function setUp()
    {
        $this->app = (new App())->get();
        $this->user = TestDatabasePopulator::populate();
        $this->registerErrorMessage = 'Username or Password field not provided.'; 
    }

    private function get($url)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => $url,
            ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;

        return $this->app->run(true);    
    }

    private function post($url,$body)
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => $url,
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded'
        ));
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);  
    }

    private function postWithToken($url,$token,$body)
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => $url,
            'HTTP_AUTHORIZATION' => "Bearer ".$token,
            'CONTENT_TYPE'       => 'application/x-www-form-urlencoded'
        ));
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);  
    }

    public function testLoginReturnsTokenWhenValidUsernameAndPasswordIsPassed()
    {
        $response = $this->post('/auth/login',['username' => 'tester','password' => 'test']);
        $result = json_decode($response->getBody(), true);
        $this->assertNotNull($result['token']);
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testLoginReturnsStatusCode401WhenCorrectUsernameWithWrongPasswordIsPassed()
    {
        $response = $this->post('/auth/login',['username' => 'tester','password' => 'tes']);
        $result = json_decode($response->getBody(), true);
        $this->assertFalse(isset($result['token']));
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testLoginReturnsStatusCode401WhenIncorrectUsernameWithPasswordIsPassed()
    {
        $response = $this->post('/auth/login',['username' => '@tester','password' => 'tes']);
        $result = json_decode($response->getBody(), true);
        $this->assertFalse(isset($result['token']));
        $this->assertSame($response->getStatusCode(), 401);
    }


    public function testGetAllEmojisReturnsOneEmoji()
    {
    
        $response = $this->get('/emojis');
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data[0]['name'], 'Suliat');
        $this->assertSame($data[0]['category'], 'sulia');
        $this->assertSame(count($data), 1);
    }

    public function testGetEmojiReturnsCorrectEmojiWithStatusCodeOf200()
    {
        $emoji = $this->user->emojis()->first();
        $response = $this->get('/emojis/'.$emoji->id);
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data['name'], $emoji->name);
        $this->assertSame($data['category'], $emoji->category->name);
    }

    public function testGetEmojiReturnsStatusCodeOf404WithMsgWhenEmojiWithPassedIdNotFound()
    {
        $response = $this->get('/emojis/as3#');
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 404);
        $this->assertSame($data['message'], 'The requested Emoji is not found.');
    }


    public function testRegisterReturnsStatusCode201WithMsgWhenUniqueUsernameAndPasswordIsPassed()
    {
        $response = $this->post('/auth/register',['username' => 'pyjac','password' => 'pyjac']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'],'User successfully created.');
        $this->assertSame($response->getStatusCode(), 201);
    }

    public function testRegisterReturnsStatusCode409WithMsgWhenAlreadyExistingUsernameWithPasswordIsPassed()
    {

        $response = $this->post('/auth/register',['username' => 'tester','password' => 'pyjac']);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'],'Username already exist.');
        $this->assertSame($response->getStatusCode(), 409);
    }

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

    public function testLogoutReturnsStatusCode400WithMsgWhenAuthorizationHeaderIsNotSet()
    {
        $response = $this->post('/auth/logout',[]);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'],'Token not provided');
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testLogoutReturnsStatusCode400WithMsgWhenAuthorizationHeaderIsSetButTokenIsNotPresent()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/auth/logout',
            'HTTP_AUTHORIZATION' => "",
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded'
        ));
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        $response = $this->app->run(true);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'],'Token not provided');
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testLogoutReturnsStatusCode400WithMsgWhenInvalidTokenIsPassed()
    {
        $response = $this->postWithToken('/auth/logout',"lblkbbbvvgjh", []);
        $result = json_decode($response->getBody(), true);
        $this->assertEquals($result['message'],'Wrong number of segments');
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

    private function getLoginTokenForTestUser()
    {
        $response = $this->post('/auth/login',['username' => 'tester','password' => 'test']);
        $result = json_decode($response->getBody(), true);

        return $result['token'];
    }


}
