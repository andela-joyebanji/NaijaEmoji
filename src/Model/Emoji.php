<?php

namespace Pyjac\NaijaEmoji\Model;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Emoji extends Eloquent
{
    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'char', 'created_at', 'updated_at', 'category', 'keywords', 'created_by'];
    protected $fillable = ['name', 'char'];

    
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

    public function scopeWithRelations($query)
    {
        return $query->with('category', 'keywords', 'created_by');   
    }
    public function scopeSearchByName($query, $emojiName)
    {
        return $query->withRelations()->where('name', 'like', "%$emojiName%");
    }
    public function scopeSearchByKeywordName($query, $keywordName)
    {
        return $query->withRelations()
                     ->joinTableLikeNameColumn('keywords', $keywordName);
                     
    }
    public function scopeSearchByCategoryName($query, $categoryName)
    {
        return $query->withRelations()
                     ->joinTableLikeNameColumn('categories', $categoryName);
    }

    public function scopeSearchByCreatorName($query, $creatorName)
    {
        return $query->withRelations()
                     ->joinTableLikeNameColumn('users', $creatorName, 'username');
    }

    public function scopeJoinTableLikeNameColumn($query, $tableName, $name, $nameColumn='name')
    {
        return $query->join($tableName, 
                            function ($join) use ($tableName, $name, $nameColumn) {
                                $join->where("$tableName.$nameColumn", 'like', "%$name%");
                            }
                        )
                    ->select(Manager::raw('emojis.*'));
    }
}
