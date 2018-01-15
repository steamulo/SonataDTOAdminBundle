<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SonataTemplatesCompilerPass implements CompilerPassInterface
{
    const FORM_THEME = 'SonataAdminBundle:Form:form_admin_fields.html.twig';
    const FILTER_THEME = 'SonataAdminBundle:Form:filter_admin_fields.html.twig';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $overwrite = $container->getParameter('sonata.admin.configuration.admin_services');

        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            if (!isset($attributes[0]['manager_type']) || $attributes[0]['manager_type'] != 'dto') {
                continue;
            }

            $definition = $container->getDefinition($id);

            if (!$definition->hasMethodCall('setFormTheme')) {
                $definition->addMethodCall('setFormTheme', [[self::FORM_THEME]]);
            }

            if (isset($overwrite[$id]['templates']['form'])) {
                $this->mergeMethodCall($definition, 'setFormTheme', $overwrite[$id]['templates']['form']);
            }

            if (!$definition->hasMethodCall('setFilterTheme')) {
                $definition->addMethodCall('setFilterTheme', [[self::FILTER_THEME]]);
            }

            if (isset($overwrite[$id]['templates']['filter'])) {
                $this->mergeMethodCall($definition, 'setFilterTheme', $overwrite[$id]['templates']['filter']);
            }
        }
    }

    /**
     * @param Definition $definition
     * @param string $name
     * @param mixed $value
     */
    public function mergeMethodCall(Definition $definition, $name, $value)
    {
        $methodCalls = $definition->getMethodCalls();
        foreach ($methodCalls as &$calls) {
            foreach ($calls as &$call) {
                if (is_string($call)) {
                    if ($call !== $name) {
                        continue 2;
                    }
                    continue 1;
                }
                $call = [array_merge($call[0], $value)];
            }
        }
        $definition->setMethodCalls($methodCalls);
    }
}
