<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Datagrid;

use Exporter\Source\SourceIteratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use ArrayIterator;

class ProxyQuerySourceIterator implements SourceIteratorInterface
{
    /**
     * @var PropertyPath[]
     */
    private $propertyPaths;

    /**
     * @var PropertyAccess
     */
    private $propertyAccessor;

    /**
     * @var string default DateTime format
     */
    private $dateTimeFormat;
    /**
     * @var ArrayIterator
     */
    private $iterator;

    /**
     * @param ProxyQuery $query
     * @param array $fields
     * @param string $dateTimeFormat
     */
    public function __construct(ProxyQuery $query, array $fields, $dateTimeFormat = 'r')
    {
        $this->iterator = new ArrayIterator($query->execute());
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        $this->propertyPaths = [];
        foreach ($fields as $name => $field) {
            if (is_string($name) && is_string($field)) {
                $this->propertyPaths[$name] = new PropertyPath($field);
            } else {
                $this->propertyPaths[$field] = new PropertyPath($field);
            }
        }

        $this->dateTimeFormat = $dateTimeFormat;
    }


    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = $this->iterator->current();
        $data = [];

        foreach ($this->propertyPaths as $name => $propertyPath) {
            try {
                $data[$name] = $this->getValue($this->propertyAccessor->getValue($current, $propertyPath));
            } catch (UnexpectedTypeException $e) {
                $data[$name] = null;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function getValue($value)
    {
        if (is_array($value) || $value instanceof \Traversable) {
            $value = null;
        } elseif ($value instanceof \DateTimeInterface) {
            $value = $value->format($this->dateTimeFormat);
        } elseif (is_object($value)) {
            $value = (string) $value;
        }

        return $value;
    }
}
