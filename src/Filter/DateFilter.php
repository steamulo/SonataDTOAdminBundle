<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Sonata\AdminBundle\Form\Type\Filter\DateType as SonataDateType;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\Criteria;

class DateFilter extends AbstractFilter
{
    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $alias
     * @param string $field
     * @param array $value
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        if (!$queryBuilder instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        if (!$value['value']) {
            return;
        }

        $criteriaValue = $value['value'];
        if (!$criteriaValue instanceof \DateTime) {
            throw new \RuntimeException('filter value must be instance of DateTime');
        }

        $criteriaType = !isset($value['type']) || !is_numeric($value['type']) ? SonataDateType::TYPE_EQUAL : $value['type'];

        $queryBuilder->addCriteria(new Criteria($field, $this->getCriteriaType($criteriaType), $criteriaValue));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'input_type' => 'datetime',
            'field_type' => DateType::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [SonataDateType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    /**
     * @param int $choiceType
     * @return int
     */
    protected function getCriteriaType($choiceType)
    {
        $choices = [
            SonataDateType::TYPE_EQUAL => Criteria::TYPE_EQUAL,
            SonataDateType::TYPE_GREATER_EQUAL => Criteria::TYPE_GREATER_EQUAL,
            SonataDateType::TYPE_GREATER_THAN => Criteria::TYPE_GREATER_THAN,
            SonataDateType::TYPE_LESS_EQUAL => Criteria::TYPE_LESS_EQUAL,
            SonataDateType::TYPE_LESS_THAN => Criteria::TYPE_LESS_THAN,
            SonataDateType::TYPE_NULL => Criteria::TYPE_NULL,
            SonataDateType::TYPE_NOT_NULL => Criteria::TYPE_NOT_NULL,
        ];

        return isset($choices[$choiceType]) ? $choices[$choiceType] : Criteria::TYPE_EQUAL;
    }
}
