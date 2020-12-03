<?php

namespace Mush\Item\DependencyInjection;

use Mush\Item\Service\ItemCycleHandlerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ItemCycleHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(ItemCycleHandlerService::class);

        foreach ($container->findTaggedServiceIds('mush.item.cycle_handler') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
