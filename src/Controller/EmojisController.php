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
    /**
     * Fetch all emojis.
     * @param  Slim\Http\Request  $request  
     * @param  Slim\Http\Response $response 
     * @param  array              $args     
     * @return Slim\Http\Response           
     */
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

	 /**
     * Get a single emoji.
     * @param  Slim\Http\Request  $request  
     * @param  Slim\Http\Response $response 
     * @param  array              $args     
     * @return Slim\Http\Response           
     */
	public function getEmoji($request, $response, $args) 
	{   
		$result = Emoji::with('category','keywords','created_by')->find($args['id']);
		if(!$result){
            return $response->withJson(["message" => "The requested Emoji is not found."],404);
        }
		$res = $result->toArray();
		$res["keywords"] = array_map(function($arr){ return $arr["name"]; },$res["keywords"]);
		$res["category"] =  $res["category"]["name"];
		$res["created_by"] =  $res["created_by"]["username"];

        return $response->withJson($res);
	}
}