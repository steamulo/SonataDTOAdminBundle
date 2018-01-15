<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class FormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * {@inheritdoc}
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

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
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));
        $fieldDescription->setFieldMapping($fieldMapping);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder($name, array $options = [])
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, null, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = [];
        $options['sonata_field_description'] = $fieldDescription;

        if (in_array($type, [
            'Sonata\AdminBundle\Form\Type\ModelType',
            'Sonata\AdminBundle\Form\Type\ModelTypeList',
            'Sonata\AdminBundle\Form\Type\ModelListType',
            'Sonata\AdminBundle\Form\Type\ModelHiddenType',
            'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
            'Sonata\AdminBundle\Form\Type\AdminType',
            'Sonata\CoreBundle\Form\Type\CollectionType',
        ])) {
            throw new \RuntimeException(sprintf('%s is not implemented', $type));
        }

        return $options;
    }
}
