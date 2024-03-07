<?php

namespace Mush\Modifier\DependencyInjection;

use Mush\Modifier\Service\ModifierRequirementHandlerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ModifierRequirementHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(ModifierRequirementHandlerService::class);

        foreach ($container->findTaggedServiceIds('mush.modifier.modifier_requirement_handler') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
