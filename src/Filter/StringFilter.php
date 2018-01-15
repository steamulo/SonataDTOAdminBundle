<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\Criteria;

class StringFilter extends AbstractFilter
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

        $criteriaValue = trim($value['value']);

        if (strlen($criteriaValue) == 0) {
            return;
        }

        $criteriaType = !isset($value['type']) || !is_numeric($value['type']) ? ChoiceType::TYPE_CONTAINS : $value['type'];

        $queryBuilder->addCriteria(new Criteria($field, $this->getCriteriaType($criteriaType), sprintf($this->getOption('format'), $criteriaValue)));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'format' => '%s',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [ChoiceType::class, [
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
            ChoiceType::TYPE_CONTAINS => Criteria::TYPE_CONTAINS,
            ChoiceType::TYPE_NOT_CONTAINS => Criteria::TYPE_NOT_CONTAINS,
            ChoiceType::TYPE_EQUAL => Criteria::TYPE_EQUAL,
        ];

        return isset($choices[$choiceType]) ? $choices[$choiceType] : Criteria::TYPE_CONTAINS;
    }
}
