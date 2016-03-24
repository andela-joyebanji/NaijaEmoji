<?php

require_once 'NaijaEmojiApiTest.php';


class NaijaEmojiApiBaseTest extends NaijaEmojiApiTest
{
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
}
