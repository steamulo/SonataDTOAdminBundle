<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface AdminSecurityInterface
{
    /**
     * @param UserInterface $user
     * @param ProxyQueryInterface $query
     */
    public function filterQueryForUser(UserInterface $user, ProxyQueryInterface $query);
}
