<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
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

        /** @var SpawnEquipmentConfig $spawnEquipmentConfig */
        foreach ($project->getSpawnEquipmentConfigs() as $spawnEquipmentConfig) {
            $holder = $projectEvent->getDaedalus()->getPlaceByNameOrThrow($spawnEquipmentConfig->getPlaceName());

            $replacedEquipment = $spawnEquipmentConfig->getReplacedEquipment();
            if (null === $replacedEquipment) {
                $this->gameEquipmentService->createGameEquipmentsFromName(
                    $spawnEquipmentConfig->getEquipmentName(),
                    $holder,
                    $projectEvent->getTags(),
                    $projectEvent->getTime(),
                    $spawnEquipmentConfig->getQuantity()
                );
            } else {
                /** @var ArrayCollection<GameEquipment> $replacedEquipments */
                $replacedEquipments = $this->gameEquipmentService->findEquipmentByNameAndDaedalus(
                    $replacedEquipment,
                    $projectEvent->getDaedalus()
                );

                if ($replacedEquipments->count() === 0) {
                    throw new \Exception(
                        sprintf(
                            'No Equipment with the name %s found in the daedalus, project (%s) can\'t replace anything.',
                            $replacedEquipment,
                            $spawnEquipmentConfig->getName()
                        )
                    );
                }

                foreach ($replacedEquipments as $replacedEquipment) {
                    $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
                        $spawnEquipmentConfig->getEquipmentName(),
                        $replacedEquipment,
                        $holder,
                        $projectEvent->getTags(),
                        $projectEvent->getTime(),
                    );
                }
            }
        }
    }
}
