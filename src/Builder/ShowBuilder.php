<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;

class ShowBuilder implements ShowBuilderInterface
{
    private $templates = [
        'array' => '@SonataAdmin/CRUD/show_array.html.twig',
        'boolean' => '@SonataAdmin/CRUD/show_boolean.html.twig',
        'date' => '@SonataIntl/CRUD/show_date.html.twig',
        'time' => '@SonataAdmin/CRUD/show_time.html.twig',
        'datetime' => '@SonataIntl/CRUD/show_datetime.html.twig',
        'text' => '@SonataAdmin/CRUD/base_show_field.html.twig',
        'email' => '@SonataAdmin/CRUD/show_email.html.twig',
        'trans' => '@SonataAdmin/CRUD/show_trans.html.twig',
        'string' => '@SonataAdmin/CRUD/base_show_field.html.twig',
        'smallint' => '@SonataIntl/CRUD/show_decimal.html.twig',
        'bigint' => '@SonataIntl/CRUD/show_decimal.html.twig',
        'integer' => '@SonataIntl/CRUD/show_decimal.html.twig',
        'decimal' => '@SonataIntl/CRUD/show_decimal.html.twig',
        'currency' => '@SonataIntl/CRUD/show_currency.html.twig',
        'percent' => '@SonataIntl/CRUD/show_percent.html.twig',
        'choice' => '@SonataAdmin/CRUD/show_choice.html.twig',
        'url' => '@SonataAdmin/CRUD/show_url.html.twig',
        'html' => '@SonataAdmin/CRUD/show_html.html.twig',
    ];

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf(
                'Please define a type for field `%s` in `%s`',
                $fieldDescription->getName(),
                get_class($admin)
            ));
        }

        $fieldMapping = [
            'id' => false,
            'fieldName' => $fieldDescription->getFieldName(),
        ];

        if (in_array($fieldDescription->getName(), $admin->getModelManager()->getIdentifierFieldNames($admin->getClass()))) {
            $fieldMapping['id'] = true;
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));
        $fieldDescription->setFieldMapping($fieldMapping);

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));

            if (!$fieldDescription->getTemplate()) {
                throw new \RuntimeException(sprintf('Unable to find template for type: %s', $fieldDescription->getType()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseList(array $options = [])
    {
        return new FieldDescriptionCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type === null) {
            $type = 'text';
        }

        $fieldDescription->setType($type);

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTemplate($type)
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }
}
