<?php

namespace Mush\Status\DependencyInjection;

use Mush\Status\Service\ChargeStrategyService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ChargeStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(ChargeStrategyService::class);

        foreach ($container->findTaggedServiceIds('mush.charge_strategy') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
