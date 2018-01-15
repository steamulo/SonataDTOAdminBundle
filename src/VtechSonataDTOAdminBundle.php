<?php

namespace Vtech\Bundle\SonataDTOAdminBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vtech\Bundle\SonataDTOAdminBundle\DependencyInjection\Compiler\SonataModelManagerCompilerPass;
use Vtech\Bundle\SonataDTOAdminBundle\DependencyInjection\Compiler\SonataTemplatesCompilerPass;

class VtechSonataDTOAdminBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SonataModelManagerCompilerPass());
        $container->addCompilerPass(new SonataTemplatesCompilerPass());
    }
}
