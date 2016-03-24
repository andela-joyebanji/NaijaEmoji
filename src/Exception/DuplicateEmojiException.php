<?php

namespace Pyjac\NaijaEmoji\Exception;

class DuplicateEmojiException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The emoji name already exist.');
    }
}
