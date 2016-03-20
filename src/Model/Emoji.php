<?php

namespace Pyjac\NaijaEmoji\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Emoji extends Eloquent {

	/**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'name','char','created_at','updated_at','category','keywords','created_by'];

	public function created_by()
	{
		return $this->belongsTo('Pyjac\NaijaEmoji\Model\User', 'created_by');
	}


	public function category()
	{
		return $this->belongsTo('Pyjac\NaijaEmoji\Model\Category');
	}

	public function keywords()
	{
		return $this->belongsToMany('Pyjac\NaijaEmoji\Model\Keyword', 'emoji_keywords');
	}
}
