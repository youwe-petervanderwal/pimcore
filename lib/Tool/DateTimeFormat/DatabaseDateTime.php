<?php

declare(strict_types=1);

namespace Pimcore\Tool\DateTimeFormat;

class DatabaseDateTime extends AbstractDateTimeFormat
{
    public function getFormat(): string
    {
        return 'Y-m-d H:i:s';
    }
}
