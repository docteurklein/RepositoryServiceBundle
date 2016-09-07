<?php

namespace DocteurKlein;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use DocteurKlein\RepositoryService\MetadataListener;

final class RepositoryServiceBundle extends Bundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_REMOVING);
        $def = new Definition(MetadataListener::class, [[]]);
        $def->addtag('doctrine.event_subscriber');
        $container->setDefinition('repository_service.metadata_listener', $def);
    }

    public function process(ContainerBuilder $container)
    {
        $doctrine = $container->get('doctrine');
        $factory = $doctrine->getManager()->getMetadataFactory();

        foreach ($factory->getAllMetadata() as $metadata) {
            $def = new Definition('Doctrine\Common\Persistence\ObjectRepository');
            $def->setFactory([new Reference('doctrine'), 'getRepository']);
            $def->setArguments([$metadata->name]);
            $id = 'repo.'.str_replace('\\', '_', $metadata->name);
            $container->setDefinition($id, $def);
        }
        $map = [];
        foreach ($container->findTaggedServiceIds('repository') as $id => $configs) {
            $def = $container->getDefinition($id);
            foreach ($configs as $config) {
                $metadata = $factory->getMetadataFor($config['for']);
                $container->setAlias($id, 'repo.'.str_replace('\\', '_', $config['for']));
                $map[$config['for']] = $def->getClass();
            }
        }

        $def = new Definition(MetadataListener::class, [$map]);
        $def->addtag('doctrine.event_subscriber');
        $container->getDefinition('repository_service.metadata_listener')->replaceArgument(0, $map);
    }
}
