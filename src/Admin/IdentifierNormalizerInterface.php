<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\Admin;

interface IdentifierNormalizerInterface
{
    /**
     * @param object $object
     * @return string
     */
    public function normalizeIdentifier($object);
}
