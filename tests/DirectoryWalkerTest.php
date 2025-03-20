<?php

use S4urp8n\TempDirectory\Watcher;

class DirectoryWalkerWOrkingDirsTest extends PHPUnit\Framework\TestCase
{

    public function testReset()
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

}