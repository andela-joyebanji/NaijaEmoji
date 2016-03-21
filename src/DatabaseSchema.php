<?php

namespace Pyjac\NaijaEmoji;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class DatabaseSchema
{
    /**
     * Create needed tables in database.
     */
    public static function createTables()
    {
        self::createUsersTable();
        self::createCategoriesTable();
        self::createEmojisTable();
        self::createKeywordsTable();
        self::createEmojiKeywordsTable();
        self::createBlacklistedTokensTable();
    }

    private static function createUsersTable()
    {
        if (!Capsule::schema()->hasTable('users')) {
            Capsule::schema()->create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username');
                $table->string('role');
                $table->string('password');
            });
        }
    }

    public static function createCategoriesTable()
    {
        if (!Capsule::schema()->hasTable('categories')) {
            Capsule::schema()->create('categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
            });
        }
    }

    public static function createEmojisTable()
    {
        if (!Capsule::schema()->hasTable('emojis')) {
            Capsule::schema()->create('emojis', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('char');
                $table->integer('category_id');
                $table->string('created_by');
                $table->timestamps();
            });
        }
    }

    public static function createKeywordsTable()
    {
        if (!Capsule::schema()->hasTable('keywords')) {
            Capsule::schema()->create('keywords', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
            });
        }
    }

    public static function createEmojiKeywordsTable()
    {
        if (!Capsule::schema()->hasTable('emoji_keywords')) {
            Capsule::schema()->create('emoji_keywords', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('emoji_id');
                $table->integer('keyword_id');
            });
        }
    }

    public static function createBlacklistedTokensTable()
    {
        if (!Capsule::schema()->hasTable('blacklisted_tokens')) {
            Capsule::schema()->create('blacklisted_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('token_jti')->unique();
            });
        }
    }

    public static function dropAllTables()
    {
        Capsule::schema()->dropIfExists('users');
        Capsule::schema()->dropIfExists('categories');
        Capsule::schema()->dropIfExists('emojis');
        Capsule::schema()->dropIfExists('keywords');
        Capsule::schema()->dropIfExists('emoji_keywords');
        Capsule::schema()->dropIfExists('blacklisted_tokens');
    }
}
