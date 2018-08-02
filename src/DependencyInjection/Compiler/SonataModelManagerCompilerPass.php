<?php

namespace Vtech\Bundle\SonataDTOAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Vtech\Bundle\SonataDTOAdminBundle\Model\ModelManager;

class SonataModelManagerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $modelManagerDefinition = $container->getDefinition(ModelManager::class);

        foreach ($container->findTaggedServiceIds('sonata.admin.dto_repository') as $serviceId => $attributes) {
            $attribute = reset($attributes);
            if (!isset($attribute['class'])) {
                throw new \RuntimeException(sprintf(
                    '%s: Missing "class" attribute for tag "sonata.admin.dto_repository"',
                    $serviceId
                ));
            }

            $modelManagerDefinition->addMethodCall('addRepository', [$attribute['class'], new Reference($serviceId)]);
        }

        foreach ($container->findTaggedServiceIds('sonata.admin.dto_identifier_normalizer') as $serviceId => $attributes) {
            $attribute = reset($attributes);
            if (!isset($attribute['class'])) {
                throw new \RuntimeException(sprintf(
                    '%s: Missing "class" attribute for tag "sonata.admin.dto_identifier_normalizer"',
                    $serviceId
                ));
            }

            $modelManagerDefinition->addMethodCall(
                'addIdentifierNormalizer',
                [$attribute['class'], new Reference($serviceId)]
            );
        }

        foreach ($container->findTaggedServiceIds('sonata.admin.dto_identifier_denormalizer') as $serviceId => $attributes) {
            $attribute = reset($attributes);
            if (!isset($attribute['class'])) {
                throw new \RuntimeException(sprintf(
                    '%s: Missing "class" attribute for tag "sonata.admin.dto_identifier_denormalizer"',
                    $serviceId
                ));
            }

            $modelManagerDefinition->addMethodCall(
                'addIdentifierDenormalizer',
                [$attribute['class'], new Reference($serviceId)]
            );
        }

        foreach ($container->findTaggedServiceIds('sonata.admin.dto_identifier_descriptor') as $serviceId => $attributes) {
            $attribute = reset($attributes);
            if (!isset($attribute['class'])) {
                throw new \RuntimeException(sprintf(
                    '%s: Missing "class" attribute for tag "sonata.admin.dto_identifier_descriptor"',
                    $serviceId
                ));
            }

            $modelManagerDefinition->addMethodCall(
                'addIdentifierDescriptor',
                [$attribute['class'], new Reference($serviceId)]
            );
        }
    }
}
