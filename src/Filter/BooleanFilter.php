<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Assert\AssertionFailedException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\Criteria;

class BooleanFilter extends AbstractFilter
{
    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $alias
     * @param string $field
     * @param array $value
     * @throws AssertionFailedException
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        if (!$queryBuilder instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        if (!in_array($value['value'], [BooleanType::TYPE_NO, BooleanType::TYPE_YES])) {
            return;
        }

        $criteriaValue = $value['value'] === BooleanType::TYPE_YES;

        $queryBuilder->addCriteria(new Criteria($field, null, $criteriaValue, $alias));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'field_type' => BooleanType::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [DefaultType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => HiddenType::class,
            'operator_options' => [],
            'label' => $this->getLabel(),
        ]];
    }
}
