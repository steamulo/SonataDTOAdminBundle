<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Repository;

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
     * @param Criteria[] $criteria
     * @param array $sorting
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function findBy(array $criteria = [], $sorting = [], $offset = 0, $limit = -1);

    /**
     * @param mixed $id
     * @return object
     */
    public function find($id);

    /**
     * @param Criteria[] $criteria
     * @return int
     */
    public function count(array $criteria = []);
}
