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
    protected $registerErrorMessage;
    protected $updateSuccessMessage;

    public function setUp()
    {
        $this->app = (new App())->get();
        $this->user = TestDatabasePopulator::populate();
        $this->registerErrorMessage = 'Username or Password field not provided.'; 
        $this->updateErrorMessage = 'The supplied emoji data is not formatted correctly.';
        $this->updateSuccessMessage = "Emoji updated successfully.";
    }

    protected function deleteWithToken($url,$token)
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD'     => 'DELETE',
            'REQUEST_URI'        => $url,
            'X-HTTP-Method-Override' => 'DELETE',
            'HTTP_AUTHORIZATION' => "Bearer ".$token,
            'CONTENT_TYPE'       => 'application/x-www-form-urlencoded'
        ));
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);  
    }

    protected function get($url)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => $url,
            ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;

        return $this->app->run(true);    
    }

    protected function post($url,$body)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => $url,
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded'
        ]);
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);  
    }

     protected function patchWithToken($url,$token,$body)
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD'         => 'PATCH',
            'REQUEST_URI'            => $url,
            'X-HTTP-Method-Override' => 'PATCH',
            'HTTP_AUTHORIZATION'     => "Bearer ".$token,
            'CONTENT_TYPE'           => 'application/x-www-form-urlencoded'
        ));
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);  
    }

    protected function postWithToken($url,$token,$body)
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

    public function testGetAllEmojisReturnsTwoEmoji()

    {
    
        $response = $this->get('/emojis');
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data[0]['name'], 'Suliat');
        $this->assertSame($data[0]['category'], 'sulia');
        $this->assertSame(count($data), 2);
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

    public function testRequestWithLoggedoutTokenReturnsStatusCode401WithMsg()
    {
        $response = $this->post('/auth/login',['username' => 'tester','password' => 'test']);
        $result = json_decode($response->getBody(), true);
        $token = $result['token'];

        $this->postWithToken('/auth/logout',$token, []);

        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__'
        ];
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 401);
        $this->assertContains("Your token has been logged out.", $result);
    }

    protected function getLoginTokenForTestUser()
    {

        $response = $this->post('/auth/login',['username' => 'tester','password' => 'test']);
        $result = json_decode($response->getBody(), true);

        return $result['token'];
    }

}
