<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PlayerLost extends AbstractPlanetSectorEventHandler
{
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->statusService = $statusService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::PLAYER_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $exploration = $event->getExploration();
        $lostPlayer = $this->randomService->getRandomPlayer($exploration->getNotLostActiveExplorators());

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $lostPlayer,
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PRIVATE,
        );

        $lostPlanetSector = $this->getLostPlanetSector($event);
        $this->addLostPlanetSectorToPlanet($lostPlanetSector, $exploration->getPlanet());

        return $this->createExplorationLog($event, parameters: [$lostPlayer->getLogKey() => $lostPlayer->getLogName()]);
    }

    private function addLostPlanetSectorToPlanet(PlanetSector $lostPlanetSector, Planet $planet): void
    {
        $planet->addSector($lostPlanetSector);
        $this->entityManager->persist($lostPlanetSector);
    }

    private function getLostPlanetSector(PlanetSectorEvent $event): PlanetSector
    {
        $lostPlanetSectorConfig = $event
            ->getExploration()
            ->getDaedalus()
            ->getGameConfig()
            ->getPlanetSectorConfigs()
            ->getBySectorName(PlanetSectorEnum::LOST)
        ;

        $lostPlanetSector = new PlanetSector($lostPlanetSectorConfig, $event->getExploration()->getPlanet());

        return $lostPlanetSector;
    }
}
