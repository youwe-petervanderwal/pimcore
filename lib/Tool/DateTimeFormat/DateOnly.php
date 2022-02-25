<?php

declare(strict_types=1);

namespace Pimcore\Tool\DateTimeFormat;

use Carbon\Carbon;

class DateOnly extends AbstractDateTimeFormat
{
    public function getFormat(): string
    {
        return 'Y-m-d';
    }

    protected function cleanup(Carbon $carbon): void
    {
        $carbon->setHour(0)->setMinute(0)->setSecond(0);
    }
}
