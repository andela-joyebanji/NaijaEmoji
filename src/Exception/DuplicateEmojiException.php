<?php

namespace Pyjac\NaijaEmoji\Exception;

class DuplicateEmojiException extends \Exception {
	
	public function __construt($message){
		parent::__construct($message);
	}
}