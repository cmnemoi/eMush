<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;

final class Artefact extends AbstractPlanetSectorEventHandler
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::ARTEFACT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $numberOfArtefactsToCreate = $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputQuantity());

        $artefacts = [];
        for ($i = 0; $i < $numberOfArtefactsToCreate; ++$i) {
            $artefactToCreate = (string) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());
            $finder = $this->randomService->getRandomElement($event->getExploration()->getExplorators()->toArray());

            $artefacts[] = $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $artefactToCreate,
                equipmentHolder: $event->getExploration()->getDaedalus()->getPlanetPlace(),
                reasons: $event->getTags(),
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
                author: $finder
            );
        }


        // for Intelligent Life Artefact event, we need to log the name of the artefact in the report
        $logParameters = [
            'target_' . $artefacts[0]->getLogKey() => $artefacts[0]->getLogName(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
