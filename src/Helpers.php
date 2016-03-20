<?php 

namespace Pyjac\NaijaEmoji;

class Helpers {
	
	public static function keysExistAndNotEmptyString($requiredStrings, $searchData){
		foreach ($requiredStrings as $key => $value) {
			if(empty($searchData[$value]) 
						|| !is_string($searchData[$value]) 
						|| !trim($searchData[$value])){
				return false;
			}
		}
		return true;
	}
}