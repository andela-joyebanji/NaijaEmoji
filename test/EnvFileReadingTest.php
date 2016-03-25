<?php

use org\bovigo\vfs\vfsStream;
use Pyjac\NaijaEmoji\App;

class EnvFileReadingTest extends PHPUnit_Framework_TestCase
{
    protected $root;
    /**
     * set up test environment
     */
    public function setUp() {
        $this->root = vfsStream::setup();
        $envFilePath = vfsStream::newFile('.env')->at($this->root);
        $envFilePath->setContent("
            APP_SECRET=some#@#$@#@#GAEEF!
            JWT_ALGORITHM=HS256
            [Database]
            driver=sqlite
            [driver=pgsql]
            host=127.0.0.1
            username=homestead
            password=secret
            port=54320
            charset=utf8
            collation=utf8_unicode_ci
            database=:memory:
            [database=Test]");
        //return $root->url();
    }

    public function testEnvLoaded()
    {
        (new App($this->root->url()))->get();
        $this->assertTrue(true);   
    }

}
