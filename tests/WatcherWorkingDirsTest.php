<?php

use S4urp8n\TempDirectory\Watcher;

class WatcherWorkingDirsTest extends PHPUnit\Framework\TestCase
{

    public function testResetAndSetAndAdd()
    {
        $thisDir = __DIR__;
        $aboveThisDir = dirname($thisDir);

        Watcher::getInstance()->setWorkingDirectories([$thisDir, $aboveThisDir]);
        $this->assertEquals([$thisDir, $aboveThisDir], Watcher::getInstance()->getWorkingDirs());

        Watcher::getInstance()->resetWorkingDirectories();
        $this->assertEquals([], Watcher::getInstance()->getWorkingDirs());

        Watcher::getInstance()->addWorkingDirectory($thisDir);
        $this->assertEquals([$thisDir], Watcher::getInstance()->getWorkingDirs());
        Watcher::getInstance()->addWorkingDirectory($thisDir);
        $this->assertEquals([$thisDir], Watcher::getInstance()->getWorkingDirs());
        Watcher::getInstance()->addWorkingDirectory($aboveThisDir);
        $this->assertEquals([$thisDir, $aboveThisDir], Watcher::getInstance()->getWorkingDirs());

        Watcher::getInstance()->resetWorkingDirectories();
        $this->assertEquals([], Watcher::getInstance()->getWorkingDirs());
    }

    public function testNotExistedDirSet()
    {
        $this->expectException(Exception::class);
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'notexisted';
        Watcher::getInstance()->setWorkingDirectories([$dir]);
    }

    public function testNotExistedDirAdd()
    {
        $this->expectException(Exception::class);
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'notexisted';
        Watcher::getInstance()->addWorkingDirectory($dir);
    }

}