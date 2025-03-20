<?php

use S4urp8n\TempDirectory\Watcher;

class WatcherMinumumSpaceTest extends PHPUnit\Framework\TestCase
{

    public function testNormal()
    {
        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory(3);
        $this->assertEquals(3, Watcher::getInstance()->getMinimumSpaceAvailableInDirectory());

        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory(30);
        $this->assertEquals(30, Watcher::getInstance()->getMinimumSpaceAvailableInDirectory());
    }

    public function testNotInteger()
    {
        $this->expectException(Exception::class);
        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory('ffff');
    }

    public function testBelowZero()
    {
        $this->expectException(Exception::class);
        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory(-1);
    }

    public function testFloat()
    {
        $this->expectException(Exception::class);
        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory(-1.2);
    }

    public function testFloat2()
    {
        $this->expectException(Exception::class);
        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory(1.2);
    }

    public function testZero()
    {
        $this->expectException(Exception::class);
        Watcher::getInstance()->setMinimumSpaceAvailableInDirectory(0);
    }

}