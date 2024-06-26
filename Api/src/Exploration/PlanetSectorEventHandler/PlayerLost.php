<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PlayerLost extends AbstractPlanetSectorEventHandler
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        private StatusServiceInterface $statusService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
    }

    public function getName(): string
    {
        return PlanetSectorEvent::PLAYER_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $daedalus = $event->getExploration()->getDaedalus();
        $exploration = $event->getExploration();

        $exploratorsWithoutACompass = $exploration
            ->getNotLostActiveExplorators()
            ->filter(static fn (Player $player) => !$player->hasEquipmentByName(ItemEnum::QUADRIMETRIC_COMPASS));

        if ($exploratorsWithoutACompass->isEmpty()) {
            $this->dispatchNothingToReportEvent($event);

            return new ExplorationLog($exploration->getClosedExploration());
        }

        $lostPlayer = $this->randomService->getRandomPlayer($exploratorsWithoutACompass);
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $lostPlayer,
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PRIVATE,
        );

        $this->playerService->changePlace($lostPlayer, $daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET_DEPTHS));

        $this->addLostPlanetSectorToPlanet($event);

        $logParameters = $this->getLogParameters($event);
        $logParameters[$lostPlayer->getLogKey()] = $lostPlayer->getLogName();

        return $this->createExplorationLog($event, $logParameters);
    }

    private function addLostPlanetSectorToPlanet(PlanetSectorEvent $event): void
    {
        $planet = $event->getExploration()->getPlanet();

        $lostPlanetSector = $this->getLostPlanetSector($event);
        $lostPlanetSector->reveal();

        $planet->addSector($lostPlanetSector);

        $this->entityManager->persist($lostPlanetSector);
    }

    private function dispatchNothingToReportEvent(PlanetSectorEvent $event): void
    {
        $config = new PlanetSectorEventConfig();
        $config->setName(PlanetSectorEvent::NOTHING_TO_REPORT);
        $config->setEventName(PlanetSectorEvent::NOTHING_TO_REPORT);

        $nothingToReportEvent = new PlanetSectorEvent(
            $event->getPlanetSector(),
            $config,
            $event->getTags(),
            $event->getTime(),
            $event->getVisibility()
        );
        $this->eventService->callEvent($nothingToReportEvent, PlanetSectorEvent::PLANET_SECTOR_EVENT);
    }

    private function getLostPlanetSector(PlanetSectorEvent $event): PlanetSector
    {
        $lostPlanetSectorConfig = $event
            ->getExploration()
            ->getDaedalus()
            ->getGameConfig()
            ->getPlanetSectorConfigs()
            ->getBySectorName(PlanetSectorEnum::LOST);

        return new PlanetSector($lostPlanetSectorConfig, $event->getExploration()->getPlanet());
    }
}
