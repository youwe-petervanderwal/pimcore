<?php

declare(strict_types=1);

namespace Pimcore\Tool\DateTimeFormat;

use Carbon\Carbon;

abstract class AbstractDateTimeFormat
{
    public abstract function getFormat(): string;

    public function getPrettyFormat(): string
    {
        return $this->getFormat();
    }

    public function parseString(string $stringValue): ?Carbon
    {
        $date = Carbon::createFromFormat($this->getFormat(), $stringValue);
        if (!$date instanceof Carbon) {
            return null;
        }
        $this->cleanup($date);
        return $date;
    }

    public function parseTimestamp(int|string $timestamp): Carbon
    {
        $date = (new Carbon())->setTimestamp((int)$timestamp);
        $this->cleanup($date);
        return $date;
    }

    public function parse(mixed $date): ?Carbon
    {
        if ($date instanceof \DateTimeInterface) {
            return new Carbon($date);
        }

        if (is_numeric($date)) {
            return $this->parseTimestamp($date);
        }

        if (is_string($date)) {
            return $this->parseString($date);
        }

        return null;
    }

    public function format(mixed $date): ?string
    {
        return $this->parse($date)?->format($this->getFormat());
    }

    public function prettyFormat(mixed $date): ?string
    {
        return $this->parse($date)?->format($this->getPrettyFormat());
    }

    protected function cleanup(Carbon $carbon): void
    {
    }
}
