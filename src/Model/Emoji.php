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

    /**
     * Get the creator of the emoji.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function created_by()
    {
        return $this->belongsTo('Pyjac\NaijaEmoji\Model\User', 'created_by');
    }

    /**
     * Get the category the emoji belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('Pyjac\NaijaEmoji\Model\Category');
    }

    /**
     * Get the keywords associated with the emoji.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function keywords()
    {
        return $this->belongsToMany('Pyjac\NaijaEmoji\Model\Keyword', 'emoji_keywords');
    }

    /**
     * Scope a query to include relations.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRelations($query)
    {
        return $query->with('category', 'keywords', 'created_by');
    }

    /**
     * Scope a query to search by emoji name.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByName($query, $emojiName)
    {
        return $query->withRelations()->where('name', 'like', "%$emojiName%");
    }

    /**
     * Scope a query to search by keyword name.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByKeywordName($query, $keywordName)
    {
        return $query->withRelations()
                     ->joinKeywordsTableLikeNameColumn($keywordName);
    }

    /**
     * Scope a query to search by category name.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByCategoryName($query, $categoryName)
    {
        return $query->withRelations()
                     ->joinTableLikeNameColumn('categories', $categoryName, "category_id");
    }

    /**
     * Scope a query to search by the creator's name.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByCreatorName($query, $creatorName)
    {
        return $query->withRelations()
                     ->joinTableLikeNameColumn('users', $creatorName, 'created_by', 'username');
    }

    /**
     * Scope a query to join search table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinTableLikeNameColumn($query, $tableName, $name, $idFieldOnEmoji = 'id', $nameColumn = 'name')
    {
        return $query->join($tableName,
                            function ($join) use ($tableName, $name, $nameColumn, $idFieldOnEmoji) {
                                $join->on("emojis.$idFieldOnEmoji", '=', "$tableName.id");
                                $join->where("$tableName.$nameColumn", 'like', "%$name%");
                            }
                        )
                    ->select(Manager::raw('emojis.*'));
    }

    /**
     * Scope query to join keyowords table.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinKeywordsTableLikeNameColumn($query, $name)
    {
        return $query
                ->join('emoji_keywords', 'emoji_keywords.emoji_id', '=', 'emojis.id')
                ->join("keywords",
                            function ($join) use ($name) {
                                $join->on('emoji_keywords.keyword_id', '=', 'keywords.id');
                                $join->where('keywords.name', 'like', "%$name%");
                            }
                        )
                    ->select(Manager::raw('emojis.*'));
    }
}
