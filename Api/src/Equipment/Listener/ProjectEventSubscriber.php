<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GameEquipmentService $equipmentService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $projectEvent): void
    {
        $project = $projectEvent->getProject();

        /** @var SpawnEquipmentConfig $spawnEquipmentConfig */
        foreach ($project->getSpawnEquipmentConfigs() as $spawnEquipmentConfig) {
            for ($i = 0; $i < $spawnEquipmentConfig->getQuantity(); ++$i) {
                $this->equipmentService->createGameEquipmentFromName(
                    $spawnEquipmentConfig->getEquipmentName(),
                    $projectEvent->getDaedalus()->getPlaceByNameOrThrow($spawnEquipmentConfig->getPlaceName()),
                    $projectEvent->getTags(),
                    $projectEvent->getTime(),
                );
            }
        }
    }
}
