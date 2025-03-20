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

    private function checkEmpty($value, string $name)
    {
        if (!$value) {
            throw new \Exception($name . " cannot be empty");
        }
    }

    private function checkSeparator($value, string $name)
    {
        if (strpos($value, static::SEPARATOR) !== false) {
            throw new \Exception(sprintf("%s '%s' cannot contain separator '%s'", $name, $value, static::SEPARATOR));
        }
    }

    private function __construct(string $prefix, int $ttlMinutes)
    {
        $this->setPrefix($prefix);
        $this->setTtlMinutes($ttlMinutes);
        $this->setCreated(date(static::DATE_FORMAT));
        $this->setId($this->generateGuid());
    }

    private function setCreated(string $created)
    {
        $this->checkEmpty($created, "Created");
        $this->checkSeparator($created, "Created");

        if (!$this->getDateTimeFromString($created)) {
            throw new \Exception('Created must be a valid date in format=' . self::DATE_FORMAT);
        }

        $this->created = $created;

        return $this;
    }

    private function setPrefix($prefix)
    {
        $this->checkEmpty($prefix, 'Prefix');
        $this->checkSeparator($prefix, 'Prefix');


        $this->prefix = $prefix;

        return $this;
    }


    private function setTtlMinutes(int $ttlMinutes)
    {
        if (!is_int($ttlMinutes) || $ttlMinutes <= 0) {
            throw new \Exception("TtlMinutes must be a positive integer");
        }

        $this->ttlMinutes = $ttlMinutes;

        return $this;
    }

    private function setId(string $id)
    {
        $this->checkEmpty($id, "Id");
        $this->checkSeparator($id, "Id");

        $this->id = $id;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    private function getDateTimeFromString($date)
    {
        try {
            $date = date_create_from_format(static::DATE_FORMAT, $date);
            if ($date) {
                return $date;
            }
        } catch (\Throwable $e) {

        }
        return null;
    }

    /**
     * @return string
     */
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
     * @return static|null
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
            $parsed->setCreated($created);
            $parsed->setId($id);

            return $parsed;

        } catch (\Throwable $e) {

        }

        return null;
    }

    /**
     * @return string
     */
    public static function generate(string $prefix, int $ttlMinutes)
    {
        return (new self($prefix, $ttlMinutes))->getName();
    }

    public function isExpired()
    {
        $parsedDate = $this->getDateTimeFromString($this->created);
        $parsedDate->modify('+' . $this->ttlMinutes . ' minutes');
        $created = $parsedDate->getTimestamp();
        $now = (new DateTime())->getTimestamp();

        return $created < $now;
    }

    private function generateGuid()
    {
        return str_replace(static::SEPARATOR, '', Uuid::uuid4()->toString());
    }

    public function __toString()
    {
        return $this->getName();
    }


}