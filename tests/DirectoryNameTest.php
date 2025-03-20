<?php

use S4urp8n\TempDirectory\DirectoryName;
use S4urp8n\TempDirectory\Watcher;

class DirectoryNameTest extends PHPUnit\Framework\TestCase
{

    public function testNormal()
    {
        $name = DirectoryName::generate('prefix', 1);
        $this->assertNotEmpty($name);
    }

    public function testBelowZero()
    {
        $this->expectException(Exception::class);
        DirectoryName::generate('prefix', -1);
    }

    public function testZero()
    {
        $this->expectException(Exception::class);
        DirectoryName::generate('prefix', 0);
    }

}