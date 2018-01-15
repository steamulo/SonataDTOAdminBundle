<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositoryInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\Criteria;

class ProxyQuery implements ProxyQueryInterface
{
    /**
     * @var AdminRepositoryInterface
     */
    private $repository;
    /**
     * @var array
     */
    private $identifiers = [];
    /**
     * @var array
     */
    private $criteriaList = [];
    /**
     * @var int
     */
    private $firstResult;
    /**
     * @var int
     */
    private $maxResults;
    /**
     * @var string
     */
    private $sortBy;
    /**
     * @var string
     */
    private $sortOrder;
    /**
     * @var int
     */
    private $uniqueParameterId;

    /**
     * ProxyQuery constructor.
     * @param AdminRepositoryInterface $repository
     */
    public function __construct(AdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->firstResult = 0;
        $this->maxResults = 0;
        $this->uniqueParameterId = 0;
    }

    /**
     * @param mixed $identifier
     */
    public function addIdentifier($identifier)
    {
        $this->identifiers[] = $identifier;
    }

    /**
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * @param Criteria $criteria
     */
    public function addCriteria(Criteria $criteria)
    {
        $this->criteriaList[] = $criteria;
    }

    /**
     * @return array
     */
    public function getCriteriaList()
    {
        return $this->criteriaList;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->repository->count($this->getCriteriaList());
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $args)
    {
        return call_user_func([$this->repository, $name], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = [], $hydrationMode = null)
    {
        return $this->repository->findBy(
            $this->getCriteriaList(),
            [
                $this->getSortBy() => $this->getSortOrder()
            ],
            $this->getFirstResult(),
            $this->getMaxResults()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $this->sortBy = $fieldMapping['fieldName'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleScalarResult()
    {
        throw new \ReflectionException('Method not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueParameterId()
    {
        return $this->uniqueParameterId++;
    }

    /**
     * {@inheritdoc}
     */
    public function entityJoin(array $associationMappings)
    {
        throw new \ReflectionException('Method not implemented');
    }
}
