<?php

use Pyjac\NaijaEmoji\Model\User;

require_once 'NaijaEmojiApiTest.php';

class NaijaEmojiApiDeleteTest extends NaijaEmojiApiTest
{
    public function testdeleteEmojiWithIdReturnsStatusCode401WithMsgWhenUserTryDeleteEmojiHeDoesNotCreate()
    {
        $emoji = User::where('id', '!=', $this->user->id)->first()->emojis()->first();
        $token = $this->getLoginTokenForTestUser();
        $response = $this->deleteWithToken('/emojis/'.$emoji->id, $token);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 401);
        $this->assertContains("You're not allowed to delete an emoji that you did not create.", $result);
    }

    public function testdeleteEmojiWithIdReturnsStatusCode200WithMsg()
    {
        $emoji = $this->user->emojis()->first();
        $token = $this->getLoginTokenForTestUser();
        $response = $this->deleteWithToken('/emojis/'.$emoji->id, $token);
        $result = (string) $response->getBody();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertContains('Emoji successfully deleted.', $result);
    }
}
