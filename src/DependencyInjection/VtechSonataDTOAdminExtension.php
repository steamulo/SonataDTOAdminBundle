<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\AbstractSonataAdminExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Vtech\Bundle\SonataDTOAdminBundle\Builder\ListBuilder;
use Vtech\Bundle\SonataDTOAdminBundle\Builder\ShowBuilder;

class VtechSonataDTOAdminExtension extends AbstractSonataAdminExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $this->fixTemplatesConfiguration($configs, $container);
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->getDefinition(ListBuilder::class)
            ->addMethodCall('setTemplates', [$config['templates']['types']['list']]);

        $container->getDefinition(ShowBuilder::class)
            ->addMethodCall('setTemplates', [$config['templates']['types']['show']]);
    }
}
