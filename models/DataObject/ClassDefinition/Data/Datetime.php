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

namespace Pimcore\Model\DataObject\ClassDefinition\Data;

use Carbon\Carbon;
use Pimcore\Db;
use Pimcore\Model;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Normalizer\NormalizerInterface;
use Pimcore\Tool\DateTimeFormat;

class Datetime extends Data implements ResourcePersistenceAwareInterface, QueryResourcePersistenceAwareInterface, TypeDeclarationSupportInterface, EqualComparisonInterface, VarExporterInterface, NormalizerInterface
{
    public const SHOW_TIMEZONE_NEVER = 'never';
    public const SHOW_TIMEZONE_WHEN_DIFFERS = 'when_differs';
    public const SHOW_TIMEZONE_ALWAYS = 'always';

    use Extension\ColumnType;
    use Extension\QueryColumnType;
    use Model\DataObject\Traits\DefaultValueTrait;

    /**
     * Static type of this element
     *
     * @internal
     *
     * @var string
     */
    public $fieldtype = 'datetime';

    /**
     * Type for the column to query
     *
     * @internal
     *
     * @var string
     */
    public $queryColumnType = 'bigint(20)';

    /**
     * Type for the column
     *
     * @internal
     *
     * @var string
     */
    public $columnType = 'bigint(20)';

    /**
     * @internal
     *
     * @var int|null
     */
    public $defaultValue;

    /**
     * @internal
     *
     * @var bool
     */
    public $useCurrentDate;

    /**
     * @var string
     */
    public $showTimezone = self::SHOW_TIMEZONE_NEVER;

    /**
     * @see ResourcePersistenceAwareInterface::getDataForResource
     *
     * @param \DateTime $data
     * @param null|Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return int|null
     */
    public function getDataForResource($data, $object = null, $params = [])
    {
        $data = $this->handleDefaultValue($data, $object, $params);

        if ($data instanceof \DateTimeInterface) {
            if ($this->getColumnType() == 'datetime') {
                return $this->getResourceDateFormatter()->format($data);
            }

            return $data->getTimestamp();
        }

        return null;
    }

    /**
     * @see ResourcePersistenceAwareInterface::getDataFromResource
     *
     * @param int $data
     * @param null|Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return \Carbon\Carbon|null
     */
    public function getDataFromResource($data, $object = null, $params = [])
    {
        if ($data) {
            if ($this->getColumnType() == 'datetime') {
                return $this->getResourceDateFormatter()->parseString($data);
            }

            return $this->getResourceDateFormatter()->parseTimestamp($data);
        }

        return null;
    }

    /**
     * @see QueryResourcePersistenceAwareInterface::getDataForQueryResource
     *
     * @param \DateTime $data
     * @param null|Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return int
     */
    public function getDataForQueryResource($data, $object = null, $params = [])
    {
        return $this->getDataForResource($data, $object, $params);
    }

    /**
     * @see Data::getDataForEditmode
     *
     * @param \DateTime|null $data
     * @param null|Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return int|null
     */
    public function getDataForEditmode($data, $object = null, $params = [])
    {
        if ($data) {
            return $this->getEditDateFormatter()->format($data);
        }

        return null;
    }

    /**
     * @see Data::getDataFromEditmode
     *
     * @param mixed $data
     * @param null|Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return \Carbon\Carbon|null
     */
    public function getDataFromEditmode($data, $object = null, $params = [])
    {
        if (!empty($data)) {
            return $this->getEditDateFormatter()->parseString($data);
        }

        return null;
    }

    /**
     * @param float $data
     * @param Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return \DateTime|null
     */
    public function getDataFromGridEditor($data, $object = null, $params = [])
    {
        return $this->getDataFromEditmode($data, $object, $params);
    }

    /**
     * @param \DateTime|null $data
     * @param Model\DataObject\Concrete|null $object
     * @param array $params
     *
     * @return int|null
     */
    public function getDataForGrid($data, $object = null, $params = [])
    {
        return $this->getDataForEditmode($data, $object, $params);
    }

    /**
     * @see Data::getVersionPreview
     *
     * @param \DateTime|null $data
     * @param null|Model\DataObject\Concrete $object
     * @param mixed $params
     *
     * @return string
     */
    public function getVersionPreview($data, $object = null, $params = [])
    {
        if ($data instanceof \DateTimeInterface) {
            return $this->getEditDateFormatter()->prettyFormat($data);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getForCsvExport($object, $params = [])
    {
        $data = $this->getDataFromObjectParam($object, $params);
        if ($data instanceof \DateTimeInterface) {
            return $this->getEditDateFormatter()->prettyFormat($data);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getDataForSearchIndex($object, $params = [])
    {
        return '';
    }

    /**
     * @return int|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        if (strlen((string)$defaultValue) > 0) {
            $this->defaultValue = $this->getResourceDateFormatter()->format($defaultValue);
        }

        return $this;
    }

    /**
     * @param bool $useCurrentDate
     *
     * @return $this
     */
    public function setUseCurrentDate($useCurrentDate)
    {
        $this->useCurrentDate = (bool)$useCurrentDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseCurrentDate(): bool
    {
        return $this->useCurrentDate;
    }

    /**
     * @return string
     */
    public function getShowTimezone(): string
    {
        return $this->showTimezone;
    }

    /**
     * @param string $showTimezone
     */
    public function setShowTimezone(string $showTimezone): void
    {
        $this->showTimezone = $showTimezone;
    }

    /**
     * {@inheritdoc}
     */
    public function isDiffChangeAllowed($object, $params = [])
    {
        return true;
    }

    /** See parent class.
     *
     * @param array $data
     * @param Model\DataObject\Concrete|null $object
     * @param mixed $params
     *
     * @return mixed
     */
    public function getDiffDataFromEditmode($data, $object = null, $params = [])
    {
        $thedata = $data[0]['data'];
        if ($thedata) {
            return $this->getEditDateFormatter()->parseString($thedata);
        }

        return null;
    }

    /** See parent class.
     * @param mixed $data
     * @param Model\DataObject\Concrete|null $object
     * @param mixed $params
     *
     * @return array|null
     */
    public function getDiffDataForEditMode($data, $object = null, $params = [])
    {
        $result = [];

        $thedata = null;
        if ($data instanceof \DatetimeInterface) {
            $thedata = $this->getEditDateFormatter()->prettyFormat($data);
        }
        $diffdata = [];
        $diffdata['field'] = $this->getName();
        $diffdata['key'] = $this->getName();
        $diffdata['type'] = $this->fieldtype;
        $diffdata['value'] = $this->getVersionPreview($data, $object, $params);
        $diffdata['data'] = $thedata;
        $diffdata['title'] = !empty($this->title) ? $this->title : $this->name;
        $diffdata['disabled'] = false;

        $result[] = $diffdata;

        return $result;
    }

    /**
     * returns sql query statement to filter according to this data types value(s)
     *
     * @param string|int $value
     * @param string $operator
     * @param array $params optional params used to change the behavior
     *
     * @return string
     */
    public function getFilterConditionExt($value, $operator, $params = [])
    {
        $timestamp = $value;

        if ($this->getColumnType() == 'datetime') {
            $value = (new DateTimeFormat\DateOnly())->format($value);
        }

        if ($operator == '=') {
            $db = Db::get();

            if ($this->getColumnType() == 'datetime') {
                $brickPrefix = $params['brickPrefix'] ? $db->quoteIdentifier($params['brickPrefix']) . '.' : '';
                $condition = 'DATE(' . $brickPrefix . '`' . $params['name'] . '`) = ' . $db->quote($value);

                return $condition;
            } else {
                $maxTime = $timestamp + (86400 - 1); //specifies the top point of the range used in the condition
                $filterField = $params['name'] ? $params['name'] : $this->getName();
                $condition = '`' . $filterField . '` BETWEEN ' . $db->quote($value) . ' AND ' . $db->quote($maxTime);

                return $condition;
            }
        }

        return parent::getFilterConditionExt($value, $operator, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetDefaultValue($object, $context = [])
    {
        if ($this->getDefaultValue()) {
            return $this->getResourceDateFormatter()->parseString($this->getDefaultValue());
        } elseif ($this->isUseCurrentDate()) {
            return new \Carbon\Carbon();
        }

        return null;
    }

    /**
     * @param \DateTimeInterface|null $oldValue
     * @param \DateTimeInterface|null $newValue
     *
     * @return bool
     */
    public function isEqual($oldValue, $newValue): bool
    {
        $oldValue = $oldValue instanceof \DateTimeInterface ? $oldValue->format('Y-m-d H:i:s') : null;
        $newValue = $newValue instanceof \DateTimeInterface ? $newValue->format('Y-m-d H:i:s') : null;

        return $oldValue === $newValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterTypeDeclaration(): ?string
    {
        return '?\\' . Carbon::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnTypeDeclaration(): ?string
    {
        return '?\\' . Carbon::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpdocInputType(): ?string
    {
        return '\\' . Carbon::class . '|null';
    }

    /**
     * {@inheritdoc}
     */
    public function getPhpdocReturnType(): ?string
    {
        return '\\' . Carbon::class . '|null';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $params = [])
    {
        if ($value instanceof Carbon) {
            return $value->getTimestamp();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($value, $params = [])
    {
        if ($value !== null) {
            return $this->getResourceDateFormatter()->parseTimestamp($value);
        }

        return null;
    }

    /**
     * overwrite default implementation to consider columnType & queryColumnType from class config
     *
     * @return array
     */
    public function resolveBlockedVars(): array
    {
        $defaultBlockedVars = [
            'fieldDefinitionsCache',
        ];

        return array_merge($defaultBlockedVars, $this->getBlockedVarsForExport());
    }

    protected function getResourceDateFormatter(): DateTimeFormat\AbstractDateTimeFormat
    {
        return new DateTimeFormat\DatabaseDateTime();
    }

    protected function getEditDateFormatter(): DateTimeFormat\AbstractDateTimeFormat
    {
        return new DateTimeFormat\NoTimezone();
    }
}
