<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GameEquipmentService $gameEquipmentService,
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

        foreach ($project->getSpawnEquipmentConfigs() as $spawnEquipmentConfig) {
            $holder = $projectEvent->getDaedalus()->getPlaceByNameOrThrow($spawnEquipmentConfig->getPlaceName());
            $this->gameEquipmentService->createGameEquipmentsFromName(
                $spawnEquipmentConfig->getEquipmentName(),
                $holder,
                $projectEvent->getTags(),
                $projectEvent->getTime(),
                $spawnEquipmentConfig->getQuantity()
            );
        }

        foreach ($project->getReplaceEquipmentConfigs() as $replaceEquipmentConfig) {
            /** @var ArrayCollection<int, GameEquipment> $replacedEquipments */
            $replacedEquipments = $this->gameEquipmentService->findEquipmentByNameAndDaedalus(
                $replaceEquipmentConfig->getReplacedEquipmentName(),
                $projectEvent->getDaedalus()
            );

            foreach ($replacedEquipments as $replacedEquipment) {
                $holder = $replacedEquipment->getHolder();
                $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
                    $replaceEquipmentConfig->getEquipmentName(),
                    $replacedEquipment,
                    $holder,
                    $projectEvent->getTags(),
                    $projectEvent->getTime(),
                );
            }
        }
    }
}
