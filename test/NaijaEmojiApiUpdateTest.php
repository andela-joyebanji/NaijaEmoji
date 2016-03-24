<?php

use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\User;

require_once 'NaijaEmojiApiTest.php';

class NaijaEmojiApiUpdateTest extends NaijaEmojiApiTest
{
    public function testPatchRequestToUpdateEmojiWithIdReturnsStatusCode200WithMsgWhenEmojiDataIsPassed()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => 'Auliat',
        'char'     => '__[:]__',
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains($this->updateSuccessMessage, $result);
        $emoji = $this->user->emojis()->first();
        $this->assertEquals($emojiData['name'], $emoji->name);
    }

    public function testPatchRequestToUpdateEmojiWithNewIdReturnsStatusCode201WithMsgWhenEmojiDataIsPassed()
    {
        $id = 1000;
        $emojiData = [
        'name'     => 'PeaceAndMercy',
        'char'     => '__[::]__',
        'category' => 'aaa',
        'keywords' => [''],
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$id, $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 201);
        $this->assertContains('Emoji created successfully.', $result);
        $emoji = Emoji::where('name', '=', 'PeaceAndMercy')->first();
        $this->assertEquals($emojiData['name'], $emoji->name);
    }

    public function testPatchRequestToUpdateEmojiWithIdReturnsStatusCode200WithMsgWhenOnlyNameIsPassed()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'name'     => 'Pyjac',
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains($this->updateSuccessMessage, $result);
        $emoji = $this->user->emojis()->first();
        $this->assertEquals($emojiData['name'], $emoji->name);
    }

    public function testPatchRequestToUpdateEmojiWithIdReturnsStatusCode200WithMsgWhenOnlyCharIsPassed()
    {
        $emoji = $this->user->emojis()->first();
        $emojiData = [
        'char'     => 'uD82EcAB00',
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emoji->id, $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains($this->updateSuccessMessage, $result);
        $emoji = $this->user->emojis()->first();
        $this->assertEquals($emojiData['char'], $emoji->char);
    }

    public function testUpdateEmojiWithIdReturnsStatusCode401WithMsgWhenUserTryUpdateEmojiHeDoesNotCreate()
    {
        $emojiByUserTwo = User::where('id', '!=', $this->user->id)->first()->emojis()->first();
        $emojiData = [
            'name'        => 'XYZ',
            'char'        => 'uD82EcAB00',
        ];
        $token = $this->getLoginTokenForTestUser();
        $response = $this->patchWithToken('/emojis/'.$emojiByUserTwo->id, $token, $emojiData);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 401);
        $this->assertContains("You're not allowed to update an emoji that you did not create.", $result);
    }
}
