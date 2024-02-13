<?php

declare(strict_types=1);

namespace Mush\Exploration\DependencyInjection;

use Mush\Exploration\Service\PlanetSectorEventHandlerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class PlanetSectorEventHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Find the definition of our context service
        $actionService = $container->findDefinition(PlanetSectorEventHandlerService::class);

        foreach ($container->findTaggedServiceIds('mush.exploration.planet_sector_event_handler') as $id => $tags) {
            $actionService->addMethodCall(
                'addStrategy',
                [new Reference($id)]
            );
        }
    }
}
