<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Sonata\AdminBundle\Filter\Filter;

abstract class AbstractFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function apply($query, $value)
    {
        $this->value = $value;
        if (is_array($value) && array_key_exists('value', $value) && null !== $value['value']) {
            $this->filter($query, null, $this->getFieldName(), $value);
        }
    }
}
