<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Repository;

interface AdminRepositorySubscriberInterface extends AdminRepositoryInterface
{
    /**
     * Indique la liste des classes supportées par ce repository
     *
     * @return string[]
     */
    public function getSupportedClass();
}
