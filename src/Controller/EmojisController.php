<?php

namespace Pyjac\NaijaEmoji\Controller;

use Illuminate\Database\Capsule\Manager;
use Pyjac\NaijaEmoji\Exception\DuplicateEmojiException;
use Pyjac\NaijaEmoji\Helpers;
use Pyjac\NaijaEmoji\Model\Category;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\Keyword;

final class EmojisController
{
    /**
     * Fetch all emojis.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     * @param array              $args
     *
     * @return Slim\Http\Response
     */
    public function getEmojis($request, $response, $args)
    {
        $result = Emoji::with('category', 'keywords', 'created_by')->get();
        $this->formatEmojiDataForClient($result);

        return $response->withJson($result);
    }

    /**
     * Get a single emoji.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     * @param array              $args
     *
     * @return Slim\Http\Response
     */
    public function getEmoji($request, $response, $args)
    {
        $result = Emoji::with('category', 'keywords', 'created_by')->find($args['id']);
        if (!$result) {
            return $response->withJson(['message' => 'The requested Emoji is not found.'], 404);
        }
        $res = $result->toArray();
        $res['keywords'] = array_map(function ($arr) { return $arr['name']; }, $res['keywords']);
        $res['category'] = $res['category']['name'];
        $res['created_by'] = $res['created_by']['username'];

        return $response->withJson($res);
    }

    /**
     * Update single emoji.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     * @param array              $args
     *
     * @return Slim\Http\Response
     */
    public function updateEmoji($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $emoji = $user->emojis()->find($args['id']);
        if (!$emoji) {
            $emoji = Emoji::find($args['id']);
            if (!$emoji) {
                return $this->create($request, $response, $args);
            }
            throw new \DomainException("You're not allowed to update an emoji that you did not create.");
        }
        $emoji->update($request->getParsedBody());

        return $response->withJson(['message' => 'Emoji updated successfully.'], 200);
    }

    /**
     * Create a emoji in the database.
     *
     * @param array                       $emojiData
     * @param Pyjac\NaijaEmoji\Model\User $user
     *
     * @return void
     */
    private function createEmoji($emojiData, $user)
    {
        Manager::transaction(function () use ($emojiData, $user) {
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
        });
    }

    /**
     * Route for creating an emoji.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     * @param array              $args
     *
     * @return Slim\Http\Response
     */
    public function create($request, $response, $args)
    {
        $emojiData = $request->getParsedBody();
        if (!$emojiData || !$this->requiredEmojiDataAreProvided($emojiData)) {
            throw new \UnexpectedValueException('The supplied emoji data is not formatted correctly.');
        }
        if (Emoji::where('name', $emojiData['name'])->first()) {
            throw new DuplicateEmojiException();
        }
        $user = $request->getAttribute('user');
        $this->createEmoji($emojiData, $user);

        return $response->withJson(['message' => 'Emoji created successfully.'], 201);
    }

    /**
     * Route for deleting an emoji.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     * @param array              $args
     *
     * @return Slim\Http\Response
     */
    public function deleteEmoji($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $emoji = $user->emojis()->find($args['id']);
        if (!$emoji) {
            throw new \DomainException("You're not allowed to delete an emoji that you did not create.");
        }
        $emoji->delete();

        return $response->withJson(['message' => 'Emoji successfully deleted.'], 200);
    }

    /**
     * Search for emojis.
     *
     * @param Slim\Http\Request  $request
     * @param Slim\Http\Response $response
     * @param array              $args
     *
     * @return Slim\Http\Response
     */
    public function searchEmoji($request, $response, $args)
    {
        $result = $this->searchEmojiBy($args['field'], $args['search']);
        $this->formatEmojiDataForClient($result);

        return $response->withJson($result);
    }

    private function searchEmojiBy($field, $searchValue)
    {
        $result = [];
        if ($field === "name") {
            $result = Emoji::searchByName($searchValue)->get();
        } elseif ($field === "keyword") {
            $result = Emoji::searchByKeywordName($searchValue)->get();
        } elseif ($field === "createdBy") {
            $result = Emoji::searchByCreatorName($searchValue)->get();
        } else {
            $result = Emoji::searchByCategoryName($searchValue)->get();
        }

        return $result;     
    }
    /**
     * Format emoji information return by Eloquent for API format.
     * 
     * @param  array $emojiData
     *  
     * @return void
     */
    private function formatEmojiDataForClient(&$emojiData)
    {
        $emojiData = $emojiData->toArray();

        foreach ($emojiData as $key => &$res) {
            $res['keywords'] = array_map(function ($arr) { return $arr['name']; }, $res['keywords']);

            $res['category'] = $res['category']['name'];
            $res['created_by'] = $res['created_by']['username'];
        }
    }

    private function requiredEmojiDataAreProvided($emojiData)
    {
        $requiredStrings = ['name', 'char', 'category'];
        if (!Helpers::keysExistAndNotEmptyString($requiredStrings, $emojiData)) {
            return false;
        }
        if (empty($emojiData['keywords'])
                        || !is_array($emojiData['keywords'])) {
            return false;
        }

        return true;
    }
}
