<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Sonata\AdminBundle\Filter\Filter;
use Vtech\Bundle\SonataDTOAdminBundle\Model\ModelManager;

abstract class AbstractFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function apply($query, $value)
    {
        $this->value = $value;
        if (is_array($value) && array_key_exists('value', $value) && null !== $value['value']) {
            $alias = null;
            if (!empty($this->getParentAssociationMappings())) {
                $alias = implode('.', $this->getParentAssociationMappings());
            }

            if ((null !== $modelManager = $this->getFieldOption('model_manager')) && is_object($value['value'])) {
                $class = $this->getFieldOption('class');
                if ($modelManager instanceof ModelManager && null !== $class && $value['value'] instanceof $class) {
                    $value['value'] = $modelManager->getDenormalizedIdentifier(
                        $class,
                        $modelManager->getNormalizedIdentifier($value['value'])
                    );

                    $this->value = $value;
                }
            }

            $this->filter($query, $alias, $this->getFieldName(), $value);
        }
    }
}
