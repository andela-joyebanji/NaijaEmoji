<?php

use Pyjac\NaijaEmoji\App;
use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\Keyword;
use Pyjac\NaijaEmoji\Model\User;
use Slim\Http\Environment;
use Slim\Http\Request;

class NaijaEmojiApi extends PHPUnit_Framework_TestCase
{
    protected $app;
    protected $user;

    public function setUp()
    {
        $this->app = (new App())->get();
        $this->user = $this->propulateDB();
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
        $this->assertSame($data['name'], 'Suliat');
        $this->assertSame($data['category'], 'sulia');
    }

    private function propulateDB()
    {
        $user = User::firstOrCreate(['username' => 'tester', 'password' => password_hash('test', PASSWORD_DEFAULT), 'role' => 'member']);
        $emojiData = [
        'name'     => 'Suliat',
        'char'     => '__[::]__',
        'category' => 'sulia',
        'keywords' => ['suzan', 'suzzy'],
        ];
        $category = Category::firstOrCreate(['name' => $emojiData['category']]);
        $emoji = new Emoji();
        $emoji->name = $emojiData['name'];
        $emoji->char = $emojiData['char'];
        $emoji->category_id = $category->id;
        $user->emojis()->save($emoji);
        $keywords = [];
        foreach ($emojiData['keywords'] as $key => $keyword) {
            $keyword = trim($keyword);
                //Skip empty keywords
            if (!$keyword) {
                continue;
            }
            $keywordModel = Keyword::firstOrCreate(['name' => $keyword]);
            $keywords[] = $keywordModel->id;
        }

        $emoji->keywords()->attach($keywords);
        //Build keywords array and create users emojis
        return $user;
    }
}
