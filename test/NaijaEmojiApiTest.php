<?php

use Pyjac\NaijaEmoji\App;
use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\User;
use org\bovigo\vfs\vfsStream;
use Slim\Http\Environment;
use Slim\Http\Request;


require_once 'TestDatabasePopulator.php';

class NaijaEmojiApiTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    protected $user;
    protected $registerErrorMessage;
    protected $updateSuccessMessage;
    protected $envRootPath;


    public function setUp()
    {
        $root = vfsStream::setup();
        $envFilePath = vfsStream::newFile('.env')->at($root);
        $envFilePath->setContent("
            APP_SECRET=some#@#$@#@#GAEEF!
            JWT_ALGORITHM=HS256
            [Database]
            driver=sqlite
            host=127.0.0.1
            charset=utf8
            collation=utf8_unicode_ci
            database=:memory:
            ");
        $this->app = (new App($root->url()))->get();
        $this->user = TestDatabasePopulator::populate();
        $this->registerErrorMessage = 'Username or Password field not provided.';
        $this->updateErrorMessage = 'The supplied emoji data is not formatted correctly.';
        $this->updateSuccessMessage = 'Emoji updated successfully.';
    }

    protected function deleteWithToken($url, $token)
    {
        $env = Environment::mock([
            'REQUEST_METHOD'         => 'DELETE',
            'REQUEST_URI'            => $url,
            'X-HTTP-Method-Override' => 'DELETE',
            'HTTP_AUTHORIZATION'     => 'Bearer '.$token,
            'CONTENT_TYPE'           => 'application/x-www-form-urlencoded',
        ]);
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

    protected function post($url, $body)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => $url,
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;

        return $this->app->run(true);
    }

    protected function patchWithToken($url, $token, $body)
    {
        $env = Environment::mock([
            'REQUEST_METHOD'         => 'PATCH',
            'REQUEST_URI'            => $url,
            'X-HTTP-Method-Override' => 'PATCH',
            'HTTP_AUTHORIZATION'     => 'Bearer '.$token,
            'CONTENT_TYPE'           => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;

        return $this->app->run(true);
    }

    protected function postWithToken($url, $token, $body)
    {
        $env = Environment::mock([
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => $url,
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE'       => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;

        return $this->app->run(true);
    }

    public function testPHPUnitWarningSuppressor()
    {
        $this->assertTrue(true);
    }

    protected function getLoginTokenForTestUser()
    {
        $response = $this->post('/auth/login', ['username' => 'tester', 'password' => 'test']);
        $result = json_decode($response->getBody(), true);

        return $result['token'];
    }
}
