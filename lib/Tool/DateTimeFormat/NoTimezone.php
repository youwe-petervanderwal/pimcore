<?php

declare(strict_types=1);

namespace Pimcore\Tool\DateTimeFormat;

class NoTimezone extends AbstractDateTimeFormat
{
    public function getFormat(): string
    {
        return 'Y-m-d\TH:i:s';
    }

    public function getPrettyFormat(): string
    {
        return 'Y-m-d H:i';
    }
}
