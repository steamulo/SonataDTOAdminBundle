<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Datagrid;

use Doctrine\Common\Collections\Criteria;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Vtech\Bundle\SonataDTOAdminBundle\Repository\AdminRepositoryInterface;

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
     * @var Criteria
     */
    private $criteria;
    /**
     * @var int
     */
    private $uniqueParameterId;
    /**
     * @var string
     */
    private $sortBy;
    /**
     * @var string
     */
    private $sortOrder;

    /**
     * ProxyQuery constructor.
     * @param AdminRepositoryInterface $repository
     */
    public function __construct(AdminRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->criteria = Criteria::create();
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
        if ($whereExpression = $criteria->getWhereExpression()) {
            $this->criteria->andWhere($whereExpression);
        }
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return int
     */
    public function getNbResults()
    {
        return $this->repository->count($this->getCriteria());
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
        return $this->repository->findBy($this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        if (!empty($parentAssociationMappings)) {
            $this->sortBy = implode('.', $parentAssociationMappings).'.'.$fieldMapping['fieldName'];

            return;
        }

        $this->sortBy = $fieldMapping['fieldName'];
        if (!empty($this->sortBy) && !empty($this->sortOrder)) {
            $this->criteria->orderBy([$this->sortBy => $this->sortOrder]);
        }
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
        if (!empty($this->sortBy) && !empty($this->sortOrder)) {
            $this->criteria->orderBy([$this->sortBy => $this->sortOrder]);
        }
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
        $this->criteria->setFirstResult($firstResult);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->criteria->getFirstResult();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->criteria->setMaxResults($maxResults);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->criteria->getMaxResults();
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
