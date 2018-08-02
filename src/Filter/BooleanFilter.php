<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;

class BooleanFilter extends AbstractFilter
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

        if (!in_array($value['value'], [BooleanType::TYPE_NO, BooleanType::TYPE_YES])) {
            return;
        }

        $criteriaValue = $value['value'] === BooleanType::TYPE_YES;

        if (!empty($alias)) {
            $field = sprintf('%s.%s', $alias, $field);
        }

        $queryBuilder->addCriteria(new Criteria(Criteria::expr()->eq($field, $criteriaValue)));
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
