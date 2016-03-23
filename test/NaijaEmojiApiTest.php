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
    protected $updateSuccessMessage;

    public function setUp()
    {
        $this->app = (new App())->get();
        $this->user = TestDatabasePopulator::populate();
        $this->registerErrorMessage = 'Username or Password field not provided.'; 
        $this->updateErrorMessage = 'The supplied emoji data is not formatted correctly.';
        $this->updateSuccessMessage = "Emoji updated successfully.";
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

     private function patchWithToken($url,$token,$body)
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
        $this->assertEquals($result['message'], 'Token not provided');
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

    public function testCreateEmojiReturnsStatusCode201WithMsgWhenWellPreparedEmojiDataIsSent()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        'category' => 'aaa',
        'keywords' => ['lol', 'hmmm']
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 201);
        $this->assertContains('Emoji created successfully.', $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutKeywords()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        'category' => 'aaa'
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutCategory()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        'keywords' => ['lol', 'hmmm']
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutChar()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'category' => 'aaa',
        'keywords' => ['lol', 'hmmm']
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutName()
    {
        $emojiData = [
        'char'     => '__[:]__',
        'category' => 'aaa',
        'keywords' => ['lol', 'hmmm']
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testPatchRequestToUpdateEmojiWithIdReturnsStatusCode200WithMsgWhenEmojiDataIsPassed()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__'
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains($this->updateSuccessMessage, $result);
        $emoji = $this->user->emojis()->first();
        $this->assertEquals($emojiData['name'], $emoji->name);
    }

    public function testPatchRequestToUpdateEmojiWithIdReturnsStatusCode200WithMsgWhenOnlyNameIsPassed()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => 'Pyjac'
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains($this->updateSuccessMessage, $result);
        $emoji = $this->user->emojis()->first();
        $this->assertEquals($emojiData['name'], $emoji->name);
    }

    public function testPatchRequestToUpdateEmojiWithIdReturnsStatusCode200WithMsgWhenOnlyCharIsPassed()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'char'     => 'uD82EcAB00'
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains($this->updateSuccessMessage, $result);
        $emoji = $this->user->emojis()->first();
        $this->assertEquals($emojiData['char'], $emoji->char);
    }
    public function testUpdateEmojiWithIdReturnsStatusCode401WithMsgWhenUserTriesUpdateEmojiHeDoesNotCreate()
    {
        $emojiByUserTwo = User::where('id','!=', $this->user->id)->first()->emojis()->first();
        $emojiData = [
            'name'        => "XYZ",
            'char'        => 'uD82EcAB00'
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emojiByUserTwo->id, $token, $emojiData);
        $result = (string)$response->getBody();
        $this->assertSame($response->getStatusCode(), 401);
        $this->assertContains("You're not allowed to update an emoji that you did not create.", $result);
    }


}
