<?php

namespace Pyjac\NaijaEmoji\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent
{
    public $timestamps = false;
    protected $fillable = ['username', 'password', 'role'];

    public function emojis()
    {
        return $this->hasMany('Pyjac\NaijaEmoji\Model\Emoji', 'created_by');
    }
}
