<?php

use S4urp8n\TempDirectory\Watcher;

class InstanceTest extends PHPUnit\Framework\TestCase
{

    public function testSameInstance()
    {
        $instance = Watcher::getInstance();
        $this->assertEquals([], Watcher::getInstance()->getWorkingDirs());
        $instance->addWorkingDirectory(__DIR__);
        $this->assertEquals([__DIR__], $instance->getWorkingDirs());

        $sameInstance = Watcher::getInstance();
        $this->assertEquals([__DIR__], $sameInstance->getWorkingDirs());
        $this->assertEquals([__DIR__], Watcher::getInstance()->getWorkingDirs());
    }

    public function testNewInstance()
    {
        $instance = Watcher::getInstance();
        $this->assertEquals([], Watcher::getInstance()->getWorkingDirs());
        $instance->addWorkingDirectory(__DIR__);
        $this->assertEquals([__DIR__], $instance->getWorkingDirs());

        $newInstance = Watcher::getNewInstance();
        $this->assertEquals([], $newInstance->getWorkingDirs());
        $this->assertEquals([__DIR__], $instance->getWorkingDirs());
        $this->assertEquals([__DIR__], Watcher::getInstance()->getWorkingDirs());

        $newInstance->addWorkingDirectory(__DIR__);
        $this->assertEquals([__DIR__], $newInstance->getWorkingDirs());
        $this->assertEquals([__DIR__], $instance->getWorkingDirs());
        $this->assertEquals([__DIR__], Watcher::getInstance()->getWorkingDirs());

    }

}