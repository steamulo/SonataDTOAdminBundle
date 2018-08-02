<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;
use Vtech\Bundle\SonataDTOAdminBundle\Form\Type\Filter\ChoiceType;

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
        if (!empty($alias)) {
            $field = sprintf('%s.%s', $alias, $field);
        }

        $queryBuilder->addCriteria(new Criteria(
            new Comparison(
                $field,
                $this->getComparisonOperator($criteriaType),
                sprintf($this->getOption('format'), $criteriaValue)
            )
        ));
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
     * @return string
     */
    protected function getComparisonOperator($choiceType)
    {
        $choices = [
            ChoiceType::TYPE_CONTAINS => Comparison::CONTAINS,
            ChoiceType::TYPE_EQUAL => Comparison::EQ,
            ChoiceType::TYPE_START_WITH => Comparison::STARTS_WITH,
            ChoiceType::TYPE_END_WITH => Comparison::ENDS_WITH,
        ];

        return isset($choices[$choiceType]) ? $choices[$choiceType] : Comparison::CONTAINS;
    }
}
