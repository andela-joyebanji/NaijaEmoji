<?php

namespace Pyjac\NaijaEmoji\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Category extends Eloquent
{
    public $timestamps = false;

    protected $fillable = ['name'];

}
