<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

class FieldDescription extends BaseFieldDescription
{
    public function __construct()
    {
        $this->fieldMapping = [];
        $this->parentAssociationMappings = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociationMapping($associationMapping)
    {
        throw new \ReflectionException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldMapping($fieldMapping)
    {
        if (!is_array($fieldMapping)) {
            throw new \RuntimeException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
        $this->parentAssociationMappings = $parentAssociationMappings;
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier()
    {
        return isset($this->fieldMapping['id']) ? $this->fieldMapping['id'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parent) {
            $object = $this->getFieldValue($object, $parent);
        }

        return $this->getFieldValue($object, $this->fieldName);
    }
}
