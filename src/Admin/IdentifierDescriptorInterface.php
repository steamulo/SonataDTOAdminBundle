<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Admin;

interface IdentifierDescriptorInterface
{
    /**
     * @return string[]
     */
    public function getIdentifierFieldNames();
}
