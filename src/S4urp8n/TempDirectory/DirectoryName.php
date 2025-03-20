<?php

namespace S4urp8n\TempDirectory;

use DateTime;
use Ramsey\Uuid\Uuid;

class DirectoryName
{
    const SEPARATOR = '-';
    const PREFIX = 'temp';
    const DATE_FORMAT = 'YmdHis';

    private $prefix;
    private $created;
    private $ttlMinutes;
    private $id;

    private function __construct($prefix, $ttlMinutes)
    {
        if (strpos($prefix, static::SEPARATOR) !== false) {
            throw new \Exception(sprintf("Prefix '%s' cannot contain separator %s", $prefix, static::SEPARATOR));
        }

        if (!$prefix) {
            throw new \Exception("Prefix cannot be empty");
        }

        if (!is_numeric($ttlMinutes) || $ttlMinutes <= 0) {
            throw new \Exception("TtlMinutes must be a positive integer");
        }

        $this->prefix = $prefix;
        $this->created = date(static::DATE_FORMAT);
        $this->ttlMinutes = $ttlMinutes;
        $this->id = str_replace(static::SEPARATOR, '', Uuid::uuid4()->toString());
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return implode(static::SEPARATOR, [
            static::PREFIX,
            $this->prefix,
            $this->created,
            $this->ttlMinutes,
            $this->id
        ]);
    }

    /**
     * @param $directory
     * @return static
     */
    public static function parse($directory)
    {
        $directory = basename($directory);

        try {

            $parts = explode(static::SEPARATOR, $directory);
            $prefix = $parts[1] ?? null;
            $created = $parts[2] ?? null;
            $ttlMinutes = $parts[3] ?? null;
            $id = $parts[4] ?? null;

            $parsed = new static($prefix, $ttlMinutes);
            $parsed->prefix = $prefix;
            $parsed->created = $created;
            $parsed->ttlMinutes = $ttlMinutes;
            $parsed->id = $id;

            if ($parsed->isValid()) {
                return $parsed;
            }

        } catch (\Throwable $e) {

        }

        return null;
    }

    /**
     * @param string $prefix
     * @param int $ttlMinutes
     * @return string
     */
    public static function generate($prefix, $ttlMinutes)
    {
        return new self($prefix, $ttlMinutes) . '';
    }

    private function getCreatedDateTime()
    {
        try {
            return date_create_from_format(static::DATE_FORMAT, $this->created);
        } catch (\Throwable $e) {

        }
        return null;
    }

    private function isValid()
    {
        $empty = !$this->prefix
            || !$this->created
            || !$this->ttlMinutes
            || !$this->id;

        if ($empty) {
            return false;
        }

        $parsedDate = $this->getCreatedDateTime();
        if (!$parsedDate || !($parsedDate instanceof DateTime)) {
            return false;
        }

        $ttlMinutes = intval($this->ttlMinutes);
        if ($ttlMinutes <= 0) {
            return false;
        }

        return true;
    }

    public function isExpired()
    {
        $parsedDate = $this->getCreatedDateTime();
        $parsedDate->modify('+' . $this->ttlMinutes . ' minutes');
        $created = $parsedDate->getTimestamp();
        $now = (new DateTime())->getTimestamp();
        return $created < $now;
    }

}