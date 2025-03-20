<?php

namespace S4urp8n\TempDirectory;

class Watcher
{
    private static $instance;
    private $minimumSpaceAvailableInDirectory = 10 * 1024 * 1024 * 1024; //10 GB
    private $workingDirs = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
    }

    public function setWorkingDirectories(array $workingDirs)
    {
        $this->resetWorkingDirectories();
        foreach ($workingDirs as $workingDir) {
            $this->addWorkingDirectory($workingDir);
        }
        return $this;
    }

    public function addWorkingDirectory($pathToDirectory)
    {
        $pathToDirectory = realpath($pathToDirectory);
        if (!$pathToDirectory || !is_dir($pathToDirectory) || !is_writable($pathToDirectory)) {
            throw new \Exception($pathToDirectory . ' is not exists or not a writable directory');
        }

        if (!in_array($pathToDirectory, $this->workingDirs)) {
            $this->workingDirs[] = $pathToDirectory;
        }

        return $this;
    }

    public function resetWorkingDirectories()
    {
        $this->workingDirs = [];
        return $this;
    }

    public function getWorkingDirs()
    {
        return $this->workingDirs;
    }

    public function getMinimumSpaceAvailableInDirectory()
    {
        return $this->minimumSpaceAvailableInDirectory;
    }

    public function setMinimumSpaceAvailableInDirectory($megabytes)
    {
        $this->minimumSpaceAvailableInDirectory = $megabytes * 1024 * 1024;
        return $this;
    }

    public function createTempDirectory($prefix, $ttlMinutes, $permissions = 0777)
    {
        $workingDir = $this->getProperWorkingDir();
        while (true) {
            $name = DirectoryName::generate($prefix, $ttlMinutes);
            $path = $workingDir . DIRECTORY_SEPARATOR . $name;
            if (file_exists($path)) {
                continue;
            }

            $created = mkdir($path, $permissions, true);
            if (!$created) {
                throw new \Exception('Unable to create temp directory ' . $path);
            }

            return $path;
        }
    }

    private function getProperWorkingDir()
    {
        foreach ($this->workingDirs as $workingDir) {
            $freeSpaceInBytes = disk_free_space($workingDir);
            if ($freeSpaceInBytes > $this->minimumSpaceAvailableInDirectory) {
                return $workingDir;
            }
        }

        throw new \Exception('No working directories haven\'t enough space');
    }

    public function clearExpired()
    {
        foreach ($this->workingDirs as $workingDir) {
            $this->clearExpiredDirectory($workingDir);
        }
    }

    private function clearExpiredDirectory($workingDir)
    {
        $directories = scandir($workingDir, SCANDIR_SORT_NONE);
        foreach ($directories as $directory) {

            if ($directory == '.' || $directory == '..') {
                continue;
            }

            $directoryName = DirectoryName::parse($directory);
            if (!$directoryName) {
                continue;
            }

            if ($directoryName->isExpired()) {
                $fullpath = $workingDir . DIRECTORY_SEPARATOR . $directoryName;
                $this->removeDirectory($fullpath);
            }

        }
    }

    private function removeDirectory(string $directory)
    {
        $output = $exitCode = '';
        $arg = escapeshellarg($directory);
        $cmd = DIRECTORY_SEPARATOR === '\\'
            ? sprintf('rmdir /s /q %s 2>&1', $arg)
            : sprintf('rm -rf %s 2>&1', $arg);

        exec($cmd, $output, $exitCode);

        if ($exitCode != 0) {
            throw new \Exception('Failed to remove directory ' . $directory);
        }
    }

}