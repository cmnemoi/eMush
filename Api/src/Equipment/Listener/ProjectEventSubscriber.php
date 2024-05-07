<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Entity\SpawnEquipmentEventConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GameEquipmentService $equipmentService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $projectEvent): void
    {
        $project = $projectEvent->getProject();

        /** @var SpawnEquipmentEventConfig $spawnEventConfig */
        foreach ($project->getActivationEventConfigs() as $spawnEventConfig) {
            for ($i = 0; $i < $spawnEventConfig->getQuantity(); $i++) {
                $this->equipmentService->createGameEquipmentFromName(
                    $spawnEventConfig->getEquipmentName(),
                    $projectEvent->getDaedalus()->getPlaceByName($spawnEventConfig->getRoomName()),
                    $projectEvent->getTags(),
                    new \DateTime(),
                    VisibilityEnum::PUBLIC
                );
            }
        }
    }
}
