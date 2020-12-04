<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Sonata\AdminBundle\Form\Type\Filter\DateType as SonataDateType;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;

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

        $this->setTime($criteriaValue);

        $criteriaType = !isset($value['type']) || !is_numeric($value['type']) ? SonataDateType::TYPE_EQUAL : $value['type'];
        switch ($criteriaType) {
            case SonataDateType::TYPE_NULL:
            case SonataDateType::TYPE_NOT_NULL:
                $criteriaValue = null;
                break;
            default:
                break;
        }

        if (!empty($alias)) {
            $field = sprintf('%s.%s', $alias, $field);
        }

        $queryBuilder->addCriteria(new Criteria(
            new Comparison($field, $this->getComparisonOperator($criteriaType), $criteriaValue)
        ));
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
     * @return string
     */
    protected function getComparisonOperator($choiceType)
    {
        $choices = [
            SonataDateType::TYPE_EQUAL => Comparison::EQ,
            SonataDateType::TYPE_GREATER_EQUAL => Comparison::GTE,
            SonataDateType::TYPE_GREATER_THAN => Comparison::GT,
            SonataDateType::TYPE_LESS_EQUAL => Comparison::LTE,
            SonataDateType::TYPE_LESS_THAN => Comparison::LT,
            SonataDateType::TYPE_NULL => Comparison::EQ,
            SonataDateType::TYPE_NOT_NULL => Comparison::NEQ,
        ];

        return isset($choices[$choiceType]) ? $choices[$choiceType] : Comparison::EQ;
    }

    protected function setTime(\DateTime $date)
    {
        if (null === $time = $this->getOption('time')) {
            return;
        }

        if (!\is_array($time)
            || !isset($time['hour'])
            || !is_int($time['hour'])
            || !isset($time['minute'])
            || !is_int($time['minute'])
            || !isset($time['second'])
            || !is_int($time['second'])) {
            throw new \RuntimeException(sprintf(
                'Invalid option `time` for field: `%s`',
                $this->getName()
            ));
        }

        $date->setTime($time['hour'], $time['minute'], $time['second']);
    }
}
