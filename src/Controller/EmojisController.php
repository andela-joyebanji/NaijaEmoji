<?php

namespace Pyjac\NaijaEmoji\Controller;

use Pyjac\NaijaEmoji\Model\Keyword;
use Pyjac\NaijaEmoji\Model\Emoji;
use Pyjac\NaijaEmoji\Model\Category;
use Illuminate\Database\Capsule\Manager;
use Pyjac\NaijaEmoji\Exception\DuplicateEmojiException;
use Pyjac\NaijaEmoji\Helpers;

final class EmojisController
{
    
    public function getEmojis($request, $response, $args) 
    {
	    $result = Emoji::with('category','keywords','created_by')->get();
	    $result = $result->toArray();

	    foreach ($result as $key => &$res) {
	        $res["keywords"] = array_map(function($arr){ return $arr["name"]; },$res["keywords"]);

	        $res["category"] =  $res["category"]["name"];
	        $res["created_by"] =  $res["created_by"]["username"];
	    }
	    
	    return $response->withJson($result);
	}
}