<?php

use Pyjac\NaijaEmoji\Model\User;

require_once 'NaijaEmojiApiTest.php';

class NaijaEmojiApiCreateTest extends NaijaEmojiApiTest
{
    public function testCreateEmojiReturnsStatusCode201WithMsgWhenWellPreparedEmojiDataIsSent()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        'category' => 'aaa',
        'keywords' => ['lol', 'hmmm'],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 201);
        $this->assertContains('Emoji created successfully.', $result);
    }

    public function testCreateEmojiReturnsStatusCode201WithMsgWhenEmojiDataWithEmptyKeywordIsPassed()
    {
        $emojiData = [
        'name'     => 'Peace',
        'char'     => '__[:]__',
        'category' => 'aaa',
        'keywords' => [''],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 201);
        $this->assertContains('Emoji created successfully.', $result);
    }

    public function testCreateEmojiReturnsStatusCode409WithMsgWhenEmojiNameAlreadyExist()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => $emoji->name,
        'char'     => '__[:]__',
        'category' => 'aaa',
        'keywords' => [''],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 409);
        $this->assertContains('The emoji name already exist.', $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutKeywords()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        'category' => 'aaa',
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutCategory()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        'keywords' => ['lol', 'hmmm'],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutChar()
    {
        $emojiData = [
        'name'     => 'Auliat',
        'category' => 'aaa',
        'keywords' => ['lol', 'hmmm'],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }

    public function testCreateEmojiReturnsStatusCode400WithMsgWhenEmojiDataIsSentWithoutName()
    {
        $emojiData = [
        'char'     => '__[:]__',
        'category' => 'aaa',
        'keywords' => ['lol', 'hmmm'],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->postWithToken('/emojis', $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 400);
        $this->assertContains($this->updateErrorMessage, $result);
    }
}
