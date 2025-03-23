<?php

use S4urp8n\TempDirectory\Watcher;

class WatcherCreateAndClearDirectoryTest extends PHPUnit\Framework\TestCase
{
    public function testCreateDirectory()
    {
        Watcher::getInstance()->addWorkingDirectory(__DIR__);

        $dirname = Watcher::getInstance()->createTempDirectory('test', 1);
        $this->assertTrue(is_dir($dirname), 'directory not exists');
        $this->assertTrue(is_writable($dirname), 'directory not writable');
    }

    protected function getDirs($dir)
    {
        return array_filter(array_map(function ($path) use ($dir) {
            if ($path == '.' || $path == '..') {
                return null;
            }

            $full = $dir . DIRECTORY_SEPARATOR . $path;

            if (!is_dir($full)) {
                return null;
            }

            return $full;

        }, scandir($dir)));
    }

    /**
     * @depends testCreateDirectory
     */
    public function testClearDirectory1()
    {
        sleep(5);
        Watcher::getInstance()->addWorkingDirectory(__DIR__);
        Watcher::getInstance()->clearExpired();

        $this->assertTrue(count($this->getDirs(__DIR__)) >= 1, 'clean of directory is not expired now');
    }

    /**
     * @depends testCreateDirectory
     * @depends testClearDirectory1
     */
    public function testClearDirectory2()
    {
        sleep(65);
        Watcher::getInstance()->addWorkingDirectory(__DIR__);
        Watcher::getInstance()->clearExpired();

        $this->assertTrue(count($this->getDirs(__DIR__)) == 0, 'clean of directory is not completed');
    }

    public function testClearAfterExecutionNoException()
    {
        Watcher::getInstance()->addWorkingDirectory(__DIR__);
        $directory = null;
        Watcher::getInstance()
            ->executeUsingTempDirectory('prefix', function ($tempdir) use (&$directory) {
                $directory = $tempdir;

                $this->assertNotEmpty($directory);
                $this->assertNotEmpty($tempdir);
                $this->assertTrue(file_exists($tempdir));
                $this->assertTrue(is_writable($tempdir));
                $this->assertTrue(is_dir($tempdir));
            }, true);

        $this->assertNotEmpty($directory);
        $this->assertFalse(file_exists($directory));
    }

}