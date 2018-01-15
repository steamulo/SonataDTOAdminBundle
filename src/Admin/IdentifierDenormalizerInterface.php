<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Admin;

interface IdentifierDenormalizerInterface
{
    /**
     * @param string $identifier
     * @return mixed
     */
    public function denormalizeIdentifier($identifier);
}
