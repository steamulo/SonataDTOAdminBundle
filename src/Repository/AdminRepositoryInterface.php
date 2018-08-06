<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Repository;

use Doctrine\Common\Collections\Criteria;

interface AdminRepositoryInterface
{
    /**
     * @param object $object
     * @return void
     */
    public function create($object);

    /**
     * @param object $object
     * @return void
     */
    public function update($object);

    /**
     * @param object $object
     * @return void
     */
    public function delete($object);

    /**
     * @param mixed[] $ids
     * @return void
     */
    public function deleteByIds($ids);

    /**
     * @param Criteria $criteria
     * @return void
     */
    public function deleteBy(Criteria $criteria);

    /**
     * @param Criteria $criteria
     * @return array
     */
    public function findBy(Criteria $criteria);

    /**
     * @param mixed $id
     * @return object
     */
    public function find($id);

    /**
     * @param Criteria $criteria
     * @return int
     */
    public function count(Criteria $criteria);
}
