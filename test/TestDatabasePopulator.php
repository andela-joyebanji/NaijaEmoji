<?php


use Illuminate\Database\Capsule\Manager as Capsule;
use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\Keyword;
use Pyjac\NaijaEmoji\Model\User;

class TestDatabasePopulator
{
    /**
     * Create test emoji for test user.
     *
     * @param Pyjac\NaijaEmoji\Model\User $user
     *
     * @return void
     */
    private static function createEmojiOwnedBy($user)
    {
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
        $keywords = self::createKeywords($emojiData['keywords']);
        $emoji->keywords()->attach($keywords);
    }

    /**
     * Create keywords.
     *
     * @param array $keywordsData
     *
     * @return array
     */
    private static function createKeywords($keywordsData)
    {
        $keywords = [];
        foreach ($keywordsData as $key => $keyword) {
            $keyword = trim($keyword);
                //Skip empty keywords
            if (!$keyword) {
                continue;
            }
            $keywordModel = Keyword::firstOrCreate(['name' => $keyword]);
            $keywords[] = $keywordModel->id;
        }

        return $keywords;
    }

    /**
     * Populate test Database with tests values.
     *
     * @return Pyjac\NaijaEmoji\Model\User
     */
    public static function populate()
    {
        Capsule::beginTransaction();

        try {
            $user = User::firstOrCreate(['username' => 'tester', 'password' => password_hash('test', PASSWORD_DEFAULT), 'role' => 'member']);
            self::createEmojiOwnedBy($user);
            Capsule::commit();
            // all good
        } catch (\Exception $e) {
            Capsule::rollback();
            throw $e;
            // something went wrong
        }

        //Build keywords array and create users emojis
        return $user;
    }
}
