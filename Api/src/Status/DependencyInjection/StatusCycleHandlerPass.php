<?php

namespace Mush\Status\DependencyInjection;

use Mush\Status\Service\StatusCycleHandlerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class StatusCycleHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(StatusCycleHandlerService::class);

        foreach ($container->findTaggedServiceIds('mush.status.cycle_handler') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
