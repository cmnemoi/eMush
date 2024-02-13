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
        $artefactsTable = $event->getOutputQuantityTable();
        if (!$artefactsTable) {
            throw new \RuntimeException('Artefact event must have an output quantity table');
        }

        $artefactToCreate = (string) $this->randomService->getSingleRandomElementFromProbaCollection($artefactsTable);

        $artefact = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $artefactToCreate,
            equipmentHolder: $event->getExploration()->getDaedalus()->getPlanetPlace(),
            reasons: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PUBLIC,
        );

        $logParameters = [
            'target_' . $artefact->getLogKey() => $artefact->getLogName(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
