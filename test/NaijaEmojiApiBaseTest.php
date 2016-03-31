<?php

use Pyjac\NaijaEmoji\Model\Keyword;

require_once 'NaijaEmojiApiTest.php';

class NaijaEmojiApiBaseTest extends NaijaEmojiApiTest
{
    public function testGetAllEmojisReturnsTwoEmoji()
    {
        $emoji = $this->user->emojis()->first();
        $response = $this->get('/emojis');
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data[0]['name'], $emoji->name);
        $this->assertSame($data[0]['category'], $emoji->category->name);
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

    public function testGetReturnsStatusCode404WithMsgWhenRequestRouteDoesNotExit()
    {
        $response = $this->get('/jsdjsdf');
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 404);
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
        $response = $this->post('/auth/login', ['username' => 'tester', 'password' => 'test']);
        $result = json_decode($response->getBody(), true);
        $token = $result['token'];

        $this->postWithToken('/auth/logout', $token, []);

        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        ];
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 401);
        $this->assertContains('Your token has been logged out.', $result);
    }

    public function testServerErrorLogs()
    {
        $handle = $this->app->getContainer()['errorHandler'];
        $response = $handle(null, new Slim\Http\Response(), new Exception());
        $this->assertSame($response->getStatusCode(), 500);

        $response = $handle(null, new Slim\Http\Response(), new PDOException());
        $this->assertSame($response->getStatusCode(), 500);
    }

    public function testSearchByName()
    {
        $name = 'Suliat';
        $response = $this->get("/emojis/name/$name");
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($data[0]['name'], $name);
        $this->assertSame(count($data), 2);
    }

    public function testSearchByKeywordName()
    {
        $name = 'suzan';
        $response = $this->get("/emojis/keyword/$name");
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertArrayHasKey($name, array_flip($data[0]['keywords']));
        $this->assertSame(count($data), 2);

        $name = 'suzzy';
        $response = $this->get("/emojis/keyword/$name");
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertArrayHasKey($name, array_flip($data[0]['keywords']));
        $this->assertSame(count($data), 2);
    }

    public function testSearchByCategory()
    {
        $name = 'sulia';
        $response = $this->get("/emojis/category/$name");
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($name, $data[0]['category']);
        $this->assertSame(count($data), 2);

        $name = 'sule';
        $response = $this->get("/emojis/category/$name");
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame(count($data), 0);
    }

    public function testSearchByCreator()
    {
        $name = 'tester';
        $response = $this->get("/emojis/createdBy/$name");
        $data = json_decode($response->getBody(), true);
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($name, $data[0]['created_by']);
        $this->assertSame(count($data), 2);

        $name = 'tester2';
        $response = $this->get("/emojis/createdBy/$name");
        $data = (string) $response->getBody();
        $this->assertContains($name, $data);
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testErrorHandlerReturnStatusCode401WhenExpiredExceptionThrown()
    {
        $handle = $this->app->getContainer()['errorHandler'];
        $response = $handle(null, new Slim\Http\Response(), new \Firebase\JWT\ExpiredException());
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testErrorHandlerReturnStatusCode409WhenDuplicateEmojiExceptionThrown()
    {
        $handle = $this->app->getContainer()['errorHandler'];
        $response = $handle(null, new Slim\Http\Response(), new Pyjac\NaijaEmoji\Exception\DuplicateEmojiException());
        $this->assertSame($response->getStatusCode(), 409);
    }
}
