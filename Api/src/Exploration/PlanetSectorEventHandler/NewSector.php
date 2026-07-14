<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Repository\PlanetSectorRepository;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;

final class NewSector extends AbstractPlanetSectorEventHandler
{
    private ExplorationServiceInterface $explorationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        ExplorationServiceInterface $explorationService,
        private PlanetSectorRepository $planetSectorRepository
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
        $this->explorationService = $explorationService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::NEW_SECTOR;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $exploration = $event->getExploration();
        $planet = $exploration->getPlanet();

        $sectorName = $this->getNewSectorName();
        $sectorConfig = $event->getDaedalus()->getGameConfig()->getPlanetSectorConfigs()->getBySectorName($sectorName);

        $sector = new PlanetSector($sectorConfig, $planet);
        $planet->addSector($sector);

        $exploration->setNextSector($sector);
        $exploration->setIgnoreNextSectorSelection(true);

        $parameters = $this->getLogParameters($event);

        return $this->createExplorationLog($event, $parameters);
    }

    public function getNewSectorName(): string
    {
        return $this->randomService->getRandomElement(PlanetSectorEnum::getSectorsThatCanBeFoundInExploration()->toArray());
    }
}
