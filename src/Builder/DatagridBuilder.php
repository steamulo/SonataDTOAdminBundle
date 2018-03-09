<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Admin\AdminSecurityInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\Pager;

class DatagridBuilder implements DatagridBuilderInterface
{
    /**
     * @var FilterFactoryInterface
     */
    protected $filterFactory;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * DatagridBuilder constructor.
     * @param FilterFactoryInterface $filterFactory
     * @param FormFactoryInterface $formFactory
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(FilterFactoryInterface $filterFactory, FormFactoryInterface $formFactory, TokenStorageInterface $tokenStorage)
    {
        $this->filterFactory = $filterFactory;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        $fieldMapping = [
            'id' => false,
            'fieldName' => $fieldDescription->getFieldName(),
        ];

        if (in_array($fieldDescription->getName(), $admin->getModelManager()->getIdentifierFieldNames($admin->getClass()))) {
            $fieldMapping['id'] = true;
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('name', $fieldDescription->getOption('name', $fieldDescription->getName()));
        $fieldDescription->setFieldMapping($fieldMapping);

        if ($fieldDescription->getOption('admin_code')) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type === null) {
            $type = 'dto_default';
        }

        $fieldDescription->setType($type);

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

        $fieldDescription->mergeOption('field_options', ['required' => false]);

        if (null !== $associationAdmin = $fieldDescription->getAssociationAdmin()) {
            $fieldDescription->mergeOption('field_options', [
                'class' => $associationAdmin->getClass(),
                'model_manager' => $associationAdmin->getModelManager(),
            ]);
        }

        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());

        if (false !== $filter->getLabel() && !$filter->getLabel()) {
            $filter->setLabel($admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        $datagrid->addFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = [])
    {
        $pager = new Pager();

        $pager->setCountColumn($admin->getModelManager()->getIdentifierFieldNames($admin->getClass()));

        $defaultOptions = [
            'csrf_protection' => false,
        ];

        $formBuilder = $this->formFactory->createNamedBuilder('filter', FormType::class, [], $defaultOptions);

        $query = $admin->createQuery();
        if ($admin instanceof AdminSecurityInterface) {
            $admin->filterQueryForUser($this->getLoggedUser(), $query);
        }

        return new Datagrid($query, $admin->getList(), $pager, $formBuilder, $values);
    }

    /**
     * @return UserInterface
     */
    private function getLoggedUser()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        if (null !== $user = $token->getUser()) {
            return $user;
        }

        return null;
    }
}
