<?php 

use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\Keyword;
use Pyjac\NaijaEmoji\Model\User;
use Illuminate\Database\Capsule\Manager as Capsule;

class TestDatabasePopulator
{
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
	}
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