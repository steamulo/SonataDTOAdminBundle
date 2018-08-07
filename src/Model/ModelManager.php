<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Admin\FieldDescription;
use Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierDenormalizerInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierDescriptorInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Admin\IdentifierNormalizerInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuery;
use Vtech\Bundle\SonataDTOAdminBundle\Datagrid\ProxyQuerySourceIterator;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositoryInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositorySubscriberInterface;

class ModelManager implements ModelManagerInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    /**
     * @var AdminRepositoryInterface[]
     */
    private $repositories = [];
    /**
     * @var IdentifierNormalizerInterface[]
     */
    private $identifierNormalizer = [];
    /**
     * @var IdentifierDenormalizerInterface[]
     */
    private $identifierDenormalizer = [];
    /**
     * @var IdentifierDescriptorInterface[]
     */
    private $identifierDescriptor = [];

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param string $class
     * @param AdminRepositoryInterface $repository
     */
    public function addRepository($class, AdminRepositoryInterface $repository)
    {
        if (isset($this->repositories[$class])) {
            throw new \InvalidArgumentException(sprintf('The class "%s" already have a repository.', $class));
        }

        $this->repositories[$class] = $repository;
    }

    /**
     * @param AdminRepositorySubscriberInterface $repository
     */
    public function addRepositorySubscriber(AdminRepositorySubscriberInterface $repository)
    {
        foreach ($repository->getSupportedClass() as $class) {
            $this->addRepository($class, $repository);
        }
    }

    /**
     * @param string $class
     * @param IdentifierNormalizerInterface $normalizer
     */
    public function addIdentifierNormalizer($class, IdentifierNormalizerInterface $normalizer)
    {
        $this->identifierNormalizer[$class] = $normalizer;
    }

    /**
     * @param string $class
     * @param IdentifierDenormalizerInterface $denormalizer
     */
    public function addIdentifierDenormalizer($class, IdentifierDenormalizerInterface $denormalizer)
    {
        $this->identifierDenormalizer[$class] = $denormalizer;
    }

    /**
     * @param string $class
     * @param IdentifierDescriptorInterface $descriptor
     */
    public function addIdentifierDescriptor($class, IdentifierDescriptorInterface $descriptor)
    {
        $this->identifierDescriptor[$class] = $descriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = [])
    {
        if (!is_string($name)) {
            throw new \RuntimeException('The name argument must be a string');
        }

        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('The class (%s) does not exists', $class));
        }

        if (!isset($options['route']['name'])) {
            $options['route']['name'] = 'edit';
        }

        if (!isset($options['route']['parameters'])) {
            $options['route']['parameters'] = [];
        }

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        if (false !== strpos($name, '.')) {
            $parentFields = explode('.', $name);
            $fieldName = array_pop($parentFields);

            $fieldDescription->setParentAssociationMappings($parentFields);
            $fieldDescription->setFieldName($fieldName);
        }

        return $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        try {
            $repository = $this->getClassRepository(get_class($object));
            $repository->create($object);
        } catch (\Exception $e) {
            throw new ModelManagerException(
                sprintf('Failed to create object: %s', get_class($object)),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($object)
    {
        try {
            $repository = $this->getClassRepository(get_class($object));
            $repository->update($object);
        } catch (\Exception $e) {
            throw new ModelManagerException(
                sprintf('Failed to update object: %s', get_class($object)),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        try {
            $repository = $this->getClassRepository(get_class($object));
            $repository->delete($object);
        } catch (\Exception $e) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', get_class($object)),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($class, array $criteria = [])
    {
        return $this->getClassRepository($class)->findBy($this->createCriteriaFromExpressions($criteria));
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy($class, array $criteria = [])
    {
        $criteria = $this->createCriteriaFromExpressions($criteria);
        $criteria->setMaxResults(1);
        $objects = $this->getClassRepository($class)->findBy($criteria);
        if (empty($objects)) {
            return null;
        }

        return reset($objects);
    }

    /**
     * {@inheritdoc}
     */
    public function find($class, $id)
    {
        if (!isset($id)) {
            return null;
        }

        $repository = $this->getClassRepository($class);

        return $repository->find($this->getDenormalizedIdentifier($class, $id));
    }

    /**
     * {@inheritdoc}
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        if (!$queryProxy instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('queryProxy must be instance of %s', ProxyQuery::class));
        }

        try {
            if ($queryProxy->hasIdentifiers()) {
                $this->getClassRepository($class)->deleteByIds($queryProxy->getIdentifiers());

                return;
            }

            $this->getClassRepository($class)->deleteBy($queryProxy->getCriteria());
        } catch (\Exception $e) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', $class),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        throw new \ReflectionException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($class, $alias = 'o')
    {
        return new ProxyQuery($this->getClassRepository($class));
    }

    /**
     * {@inheritdoc}
     */
    public function getModelIdentifier($class)
    {
        $identifiers = $this->getIdentifierFieldNames($class);

        return reset($identifiers);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierValues($model)
    {
        $values = [];
        foreach ($this->getIdentifierFieldNames(get_class($model)) as $fieldName) {
            $values[] = $this->propertyAccessor->getValue($model, $fieldName);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames($class)
    {
        if (null !== $descriptor = $this->getClassIdentifierDescriptor($class)) {
            $descriptor->getIdentifierFieldNames();
        }

        $reflection = new \ReflectionClass($class);
        if ($reflection->hasMethod('getId')
            && $reflection->getMethod('getId')->isPublic()) {
            return ['id'];
        }

        if ($reflection->hasProperty('id')
            && $reflection->getProperty('id')->isPublic()) {
            return ['id'];
        }

        throw new \RuntimeException(sprintf('Unable to find identifier for model: %s', $class));
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizedIdentifier($model)
    {
        if (is_scalar($model)) {
            throw new \RuntimeException('Invalid argument, object or null required');
        }

        if (!$model) {
            return null;
        }

        $identifiers = $this->getIdentifierValues($model);
        if (empty(array_filter($identifiers))) {
            return null;
        }

        if (null !== $normalizer = $this->getClassIdentifierNormalizer(get_class($model))) {
            return $normalizer->normalizeIdentifier($model);
        }

        return implode('~', $identifiers);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlsafeIdentifier($model)
    {
        return $this->getNormalizedIdentifier($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getModelInstance($class)
    {
        $r = new \ReflectionClass($class);
        if ($r->isAbstract()) {
            throw new \RuntimeException(sprintf('Cannot initialize abstract class: %s', $class));
        }

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getModelCollectionInstance($class)
    {
        return new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(sprintf(
                'Expected collection of type "%s", "%s" given',
                Collection::class,
                is_object($collection) ? get_class($collection) : gettype($collection)
            ));
        }

        return $collection->removeElement($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionAddElement(&$collection, &$element)
    {
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(sprintf(
                'Expected collection of type "%s", "%s" given',
                Collection::class,
                is_object($collection) ? get_class($collection) : gettype($collection)
            ));
        }

        return $collection->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionHasElement(&$collection, &$element)
    {
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(sprintf(
                'Expected collection of type "%s", "%s" given',
                Collection::class,
                is_object($collection) ? get_class($collection) : gettype($collection)
            ));
        }

        return $collection->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionClear(&$collection)
    {
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(sprintf(
                'Expected collection of type "%s", "%s" given',
                Collection::class,
                is_object($collection) ? get_class($collection) : gettype($collection)
            ));
        }

        return $collection->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();
        /** @var FieldDescriptionInterface $sortByField */
        $sortByField = $values['_sort_by'];

        if ($fieldDescription->getName() == $sortByField->getName()
            || $sortByField->getName() === $fieldDescription->getOption('sortable')) {
            if ($values['_sort_order'] == 'ASC') {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
        } else {
            $values['_sort_order'] = 'ASC';
        }

        $values['_sort_by'] = is_string($fieldDescription->getOption('sortable')) ?
            $fieldDescription->getOption('sortable') : $fieldDescription->getName();

        return ['filter' => $values];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSortValues($class)
    {
        return [
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class),
            '_page' => 1,
            '_per_page' => 25,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelReverseTransform($class, array $array = [])
    {
        $instance = $this->getModelInstance($class);
        foreach ($array as $name => $value) {
            $this->propertyAccessor->setValue($instance, $name, $value);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuery($query)
    {
        if (!$query instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator(
        DatagridInterface $datagrid,
        array $fields,
        $firstResult = null,
        $maxResult = null
    ) {
        $datagrid->buildPager();
        $query = $datagrid->getQuery();
        if (!$query instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        $query->setFirstResult($firstResult);
        $query->setMaxResults($maxResult);

        return new ProxyQuerySourceIterator($query, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields($class)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        /** @var FieldDescriptionInterface $sortByField */
        $sortByField = $values['_sort_by'];

        $values['_sort_by'] = $sortByField->getName();
        if (null !== $page) {
            $values['_page'] = $page;
        }

        return ['filter' => $values];
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx)
    {
        if (!$query instanceof ProxyQuery) {
            throw new \RuntimeException(sprintf('query must be instance of %s', ProxyQuery::class));
        }

        foreach ($idx as $id) {
            $query->addIdentifier($this->getDenormalizedIdentifier($class, $id));
        }
    }

    /**
     * @param string $class
     * @return AdminRepositoryInterface
     */
    protected function getClassRepository($class)
    {
        if (!isset($this->repositories[$class])) {
            throw new \RuntimeException(sprintf('No repository defined for class %s', $class));
        }

        return $this->repositories[$class];
    }

    /**
     * @param string $class
     * @return IdentifierNormalizerInterface
     */
    protected function getClassIdentifierNormalizer($class)
    {
        if (!isset($this->identifierNormalizer[$class])) {
            return null;
        }

        return $this->identifierNormalizer[$class];
    }

    /**
     * @param string $class
     * @param string $identifier
     * @return mixed
     */
    public function getDenormalizedIdentifier($class, $identifier)
    {
        if (null !== $denormalizer = $this->getClassIdentifierDenormalizer($class)) {
            return $denormalizer->denormalizeIdentifier($identifier);
        }

        if (strpos($identifier, '~') !== false) {
            return array_combine($this->getIdentifierFieldNames($class), explode('~', $identifier));
        }

        return $identifier;
    }

    /**
     * @param string $class
     * @return IdentifierDescriptorInterface
     */
    protected function getClassIdentifierDescriptor($class)
    {
        if (!isset($this->identifierDescriptor[$class])) {
            return null;
        }

        return $this->identifierDescriptor[$class];
    }

    /**
     * @param string $class
     * @return IdentifierDenormalizerInterface
     */
    protected function getClassIdentifierDenormalizer($class)
    {
        if (!isset($this->identifierDenormalizer[$class])) {
            return null;
        }

        return $this->identifierDenormalizer[$class];
    }

    /**
     * @param array $expressions
     * @return Criteria
     */
    private function createCriteriaFromExpressions(array $expressions)
    {
        $criteria = Criteria::create();
        foreach ($expressions as $expression) {
            if ($expression instanceof Criteria) {
                return $expression;
            }

            if ($expression instanceof Expression) {
                $criteria->andWhere($expression);
            }
        }

        return $criteria;
    }
}
