<?php

declare(strict_types=1);

namespace Mush\Equipment\DependencyInjection;

use Mush\Equipment\Service\WeaponEffectHandlerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class WeaponEffectHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(WeaponEffectHandlerService::class);

        foreach ($container->findTaggedServiceIds('mush.equipment.weapon_effect_handler') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
