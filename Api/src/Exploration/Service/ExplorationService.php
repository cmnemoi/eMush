<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;

final class ExplorationService implements ExplorationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private PlanetServiceInterface $planetService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        PlanetServiceInterface $planetService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->planetService = $planetService;
        $this->randomService = $randomService;
    }

    public function createExploration(PlayerCollection $players, GameEquipment $explorationShip, int $numberOfSectorsToVisit, array $reasons): Exploration
    {
        $explorator = $players->first();
        if (!$explorator) {
            throw new \RuntimeException('You need a non-empty explorator collection to create an exploration');
        }
        $daedalus = $explorator->getDaedalus();

        $planet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);
        if ($planet === null) {
            throw new \RuntimeException('There should be one planet in daedalus orbit');
        }

        $exploration = new Exploration($planet);
        $exploration->setExplorators($players);
        $exploration->getClosedExploration()->setClosedExplorators($players->map(static fn (Player $player) => $player->getPlayerInfo()->getClosedPlayer())->toArray());
        $exploration->setNumberOfSectionsToVisit($numberOfSectorsToVisit);

        if ($exploration->getNumberOfSectionsToVisit() < 1) {
            throw new \RuntimeException('You cannot visit less than 1 sector');
        }
        $exploration->setShipUsedName($explorationShip->getName());
        $exploration->setStartPlaceName($explorationShip->getPlace()->getName());

        $this->persist([$exploration]);

        $explorationEvent = new ExplorationEvent(
            exploration: $exploration,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_STARTED);

        return $exploration;
    }

    public function closeExploration(Exploration $exploration, array $reasons): void
    {
        $closedExploration = $exploration->getClosedExploration();

        $explorationEvent = new ExplorationEvent(
            exploration: $exploration,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_FINISHED);

        $closedExploration->finishExploration();

        $this->delete([$exploration]);
        if (\in_array(ExplorationEvent::ALL_EXPLORATORS_STUCKED, $reasons, true)) {
            $this->delete([$closedExploration]);
        }
    }

    public function dispatchLandingEvent(Exploration $exploration): Exploration
    {
        $planet = $exploration->getPlanet();

        $landingSectorConfig = $this->findPlanetSectorConfigBySectorName(PlanetSectorEnum::LANDING);
        $landingSector = new PlanetSector($landingSectorConfig, $planet);

        if ($exploration->hasAPilotAlive()) {
            $eventConfig = $this->findPlanetSectorEventConfigByName(PlanetSectorEvent::NOTHING_TO_REPORT);

            $planetSectorEvent = new PlanetSectorEvent(
                planetSector: $landingSector,
                config: $eventConfig,
            );
            $planetSectorEvent->addTag('always_successful_thanks_to_pilot');
        } else {
            $eventKey = $this->drawPlanetSectorEvent($landingSector, $exploration);
            $eventConfig = $this->findPlanetSectorEventConfigByName($eventKey);

            $planetSectorEvent = new PlanetSectorEvent(
                planetSector: $landingSector,
                config: $eventConfig,
            );
        }
        $this->eventService->callEvent($planetSectorEvent, PlanetSectorEvent::PLANET_SECTOR_EVENT);

        return $exploration;
    }

    public function dispatchExplorationEvent(Exploration $exploration): Exploration
    {
        $closedExploration = $exploration->getClosedExploration();
        $planet = $exploration->getPlanet();

        /** @var PlanetSector $sector */
        $sector = $this->randomService->getRandomPlanetSectorsToVisit($planet, 1)->first();
        $sector->visit();
        $closedExploration->addExploredSectorKey($sector->getName());

        $eventKey = $this->drawPlanetSectorEvent($sector, $exploration);
        $eventConfig = $this->findPlanetSectorEventConfigByName($eventKey);

        $event = new PlanetSectorEvent(
            planetSector: $sector,
            config: $eventConfig,
        );
        $this->eventService->callEvent($event, PlanetSectorEvent::PLANET_SECTOR_EVENT);

        return $exploration;
    }

    /**
     * @psalm-suppress PossiblyNullArgument
     */
    public function getDummyExplorationForLostPlayer(ClosedExploration $closedExploration): Exploration
    {
        /** @var Daedalus $daedalus */
        $daedalus = $closedExploration->getDaedalusInfo()->getDaedalus();

        /** @var Planet $planet */
        $planet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);

        $dummyExploration = new Exploration($planet);
        $dummyExploration->setCreatedAt($closedExploration->getCreatedAt());
        $dummyExploration->setUpdatedAt($closedExploration->getUpdatedAt());

        /** @var array<int, Player> $explorators */
        $explorators = $closedExploration
            ->getClosedExplorators()
            ->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getPlayer())
            ->filter(static fn (?Player $player) => $player instanceof Player)
            ->toArray();

        $dummyExploration->setExplorators(new PlayerCollection($explorators));
        foreach ($closedExploration->getLogs() as $log) {
            $dummyExploration->getClosedExploration()->addLog($log);
        }

        $dummyExploration->getClosedExploration()->finishExploration();

        return $dummyExploration;
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    private function drawPlanetSectorEvent(PlanetSector $sector, Exploration $exploration): string
    {   
        $daedalus = $exploration->getDaedalus();
        $sectorEvents = clone $sector->getExplorationEvents();
        if ($exploration->hasAFunctionalCompass()) {
            $sectorEvents->remove(PlanetSectorEvent::AGAIN);
        }
        if ($exploration->hasAFunctionalBabelModule() && $sector->getName() === PlanetSectorEnum::INTELLIGENT) {
            $newProbability = $sectorEvents->getElementProbability(PlanetSectorEvent::ARTEFACT) * 2;
            $sectorEvents->setElementProbability(PlanetSectorEvent::ARTEFACT, $newProbability);
        }
        if ($daedalus->hasFinishedProject(ProjectName::ICARUS_ANTIGRAV_PROPELLER) && $sector->getName() === PlanetSectorEnum::LANDING) {
            $newProbability = $sectorEvents->getElementProbability(PlanetSectorEvent::NOTHING_TO_REPORT) * 2;
            $sectorEvents->setElementProbability(PlanetSectorEvent::NOTHING_TO_REPORT, $newProbability);
        }

        return (string) $this->randomService->getSingleRandomElementFromProbaCollection($sectorEvents);
    }

    private function findPlanetSectorConfigBySectorName(string $sectorName): PlanetSectorConfig
    {
        $planetSector = $this->entityManager->getRepository(PlanetSectorConfig::class)->findOneBySectorName($sectorName);
        if ($planetSector === null) {
            throw new \RuntimeException('PlanetSectorConfig not found for sector ' . $sectorName);
        }

        return $planetSector;
    }

    private function findPlanetSectorEventConfigByName(string $eventKey): PlanetSectorEventConfig
    {
        $eventConfig = $this->entityManager->getRepository(PlanetSectorEventConfig::class)->findOneByName($eventKey);
        if (!$eventConfig) {
            throw new \RuntimeException('PlanetSectorEventConfig not found for event ' . $eventKey);
        }

        return $eventConfig;
    }

    private function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }
}
