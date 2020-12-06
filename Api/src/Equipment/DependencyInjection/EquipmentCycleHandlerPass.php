<?php

namespace Mush\Equipment\DependencyInjection;

use Mush\Equipment\Service\EquipmentCycleHandlerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EquipmentCycleHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(EquipmentCycleHandlerService::class);

        foreach ($container->findTaggedServiceIds('mush.equipment.cycle_handler') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
