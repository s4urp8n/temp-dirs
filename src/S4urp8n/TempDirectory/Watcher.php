<?php

namespace S4urp8n\TempDirectory;

class Watcher
{

    const MINUTES_IN_HOUR = 60;
    const MINUTES_IN_DAY = self::MINUTES_IN_HOUR * 24;
    const MINUTES_IN_WEEK = self::MINUTES_IN_DAY * 7;
    const MINUTES_IN_MONTH = self::MINUTES_IN_DAY * 30;
    const MINUTES_IN_YEAR = self::MINUTES_IN_DAY * 365;

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
        if (!is_integer($megabytes) || $megabytes < 1) {
            throw new \Exception('Megabytes must be an positive integer');
        }

        $this->minimumSpaceAvailableInDirectory = $megabytes;
        return $this;
    }

    public function createTempDirectory($prefix, $ttlMinutes, $permissions = 0777)
    {
        $this->checkWorkingDirs();

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
            if ($freeSpaceInBytes < $this->minimumSpaceAvailableInDirectory * 1024 * 1024) {
                return $workingDir;
            }
        }

        throw new \Exception('No working directories having enough space');
    }

    private function checkWorkingDirs()
    {
        if (!$this->workingDirs) {
            throw new \Exception('No working directories set');
        }
    }

    public function clearExpired()
    {
        $this->checkWorkingDirs();

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

            $fullPath = $workingDir . DIRECTORY_SEPARATOR . $directory;
            if (!is_dir($fullPath)) {
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

    public function removeDirectory(string $directory)
    {
        if (!file_exists($directory)) {
            return;
        }

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

    public function executeUsingTempDirectory(string $prefix, $callback, bool $removeAfterException = true)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Callback must be a callable function');
        }

        $tempDirectory = static::getInstance()->createTempDirectory($prefix, self::MINUTES_IN_YEAR);

        try {
            $result = $callback($tempDirectory);
            $this->removeDirectory($tempDirectory);
            return $result;
        } catch (\Throwable $throwable) {
            if ($removeAfterException) {
                $this->removeDirectory($tempDirectory);
            }
            throw $throwable;
        }
    }

}