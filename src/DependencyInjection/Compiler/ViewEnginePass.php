<?php

namespace Flex\DependencyInjection\Compiler;

use Flex\View\ViewEngineManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ViewEnginePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if(!$container->has(ViewEngineManager::class)){
            return;
        }

        $definition = $container->findDefinition(ViewEngineManager::class);
        $taggedServices = $container->findTaggedServiceIds('view.engine');

        foreach($taggedServices as $id => $tags){
            foreach($tags as $attributes){
                $definition->addMethodCall('addEngine', [$attributes['extensions'], new Reference($id)]);
            }
        }
    }
}