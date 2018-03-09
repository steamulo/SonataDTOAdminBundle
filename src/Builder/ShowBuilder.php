<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;

class ShowBuilder implements ShowBuilderInterface
{
    private $templates = [
        'array' => 'SonataAdminBundle:CRUD:show_array.html.twig',
        'boolean' => 'SonataAdminBundle:CRUD:show_boolean.html.twig',
        'date' => 'SonataAdminBundle:CRUD:show_date.html.twig',
        'time' => 'SonataAdminBundle:CRUD:show_time.html.twig',
        'datetime' => 'SonataAdminBundle:CRUD:show_datetime.html.twig',
        'text' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'textarea' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'email' => 'SonataAdminBundle:CRUD:show_email.html.twig',
        'trans' => 'SonataAdminBundle:CRUD:show_trans.html.twig',
        'string' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'smallint' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'bigint' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'integer' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'decimal' => 'SonataAdminBundle:CRUD:show_string.html.twig',
        'currency' => 'SonataAdminBundle:CRUD:show_currency.html.twig',
        'percent' => 'SonataAdminBundle:CRUD:show_percent.html.twig',
        'choice' => 'SonataAdminBundle:CRUD:show_choice.html.twig',
        'url' => 'SonataAdminBundle:CRUD:show_url.html.twig',
        'html' => 'SonataAdminBundle:CRUD:show_html.html.twig',
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
