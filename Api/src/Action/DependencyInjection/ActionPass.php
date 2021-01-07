<?php

namespace Mush\Action\DependencyInjection;

use Mush\Action\Service\ActionStrategyService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(ActionStrategyService::class);

        foreach ($container->findTaggedServiceIds('mush.action') as $id => $tags) {
            $actionService->addMethodCall(
                'addAction',
                [new Reference($id)]
            );
        }
    }
}
