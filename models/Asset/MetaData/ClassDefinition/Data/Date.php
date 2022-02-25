<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Model\Asset\MetaData\ClassDefinition\Data;

use Pimcore\Tool\DateTimeFormat;

class Date extends Data
{
    /**
     * @param mixed $value
     * @param array $params
     *
     * @return null|string
     */
    public function marshal($value, $params = [])
    {
        if ($value && !is_numeric($value)) {
            $value = strtotime($value);
        }

        return $value;
    }

    public function getDataForEditMode($data, $params = [])
    {
        return $this->getDateFormatter()->format($data);
    }

    public function getDataFromEditMode($data, $params = [])
    {
        return $this->getDateFormatter()->parseString($data)->getTimestamp();
    }

    public function getDataForListfolderGrid($data, $params = [])
    {
        return $this->getDataForEditMode($data, $params);
    }

    public function getDataFromListfolderGrid($data, $params = [])
    {
        return $this->getDataFromEditMode($data, $params);
    }

    /**
     * @param mixed $value
     * @param array $params
     *
     * @return string
     */
    public function getVersionPreview($value, $params = [])
    {
        return $this->getDateFormatter()->format($value) ?? '';
    }


    protected function getDateFormatter(): DateTimeFormat\AbstractDateTimeFormat
    {
        return new DateTimeFormat\DateOnly();
    }
}
