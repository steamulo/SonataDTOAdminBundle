<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Repository;

use Assert\Assertion;

class Criteria
{
    const TYPE_CONTAINS = 1;
    const TYPE_NOT_CONTAINS = 2;
    const TYPE_EQUAL = 3;
    const TYPE_GREATER_EQUAL = 4;
    const TYPE_GREATER_THAN = 5;
    const TYPE_LESS_EQUAL = 6;
    const TYPE_LESS_THAN = 7;
    const TYPE_NULL = 8;
    const TYPE_NOT_NULL = 9;
    const TYPE_IN = 10;

    /**
     * @var string
     */
    protected $fieldName;
    /**
     * @var int
     */
    protected $type;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var string|null
     */
    protected $parentAlias;

    /**
     * @param string $fieldName
     * @param int $type
     * @param mixed $value
     * @param string|null $parentAlias
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($fieldName, $type, $value, $parentAlias = null)
    {
        Assertion::string($fieldName);
        Assertion::nullOrInArray($type, [
            self::TYPE_CONTAINS,
            self::TYPE_NOT_CONTAINS,
            self::TYPE_EQUAL,
            self::TYPE_GREATER_EQUAL,
            self::TYPE_GREATER_THAN,
            self::TYPE_LESS_EQUAL,
            self::TYPE_LESS_THAN,
            self::TYPE_NULL,
            self::TYPE_NOT_NULL,
        ]);
        Assertion::nullOrString($parentAlias);

        $this->fieldName = $fieldName;
        $this->type = $type;
        $this->value = $value;
        $this->parentAlias = $parentAlias;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return null|string
     */
    public function getParentAlias()
    {
        return $this->parentAlias;
    }
}
