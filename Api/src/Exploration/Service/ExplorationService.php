<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

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
        $exploration->getClosedExploration()->setClosedExplorators($players->map(fn (Player $player) => $player->getPlayerInfo()->getClosedPlayer())->toArray());
        $exploration->setNumberOfSectionsToVisit(min($numberOfSectorsToVisit, $planet->getUnvisitedSectors()->count()));

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
        if (in_array(ExplorationEvent::ALL_EXPLORATORS_STUCKED, $reasons)) {
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
            if (!$eventConfig) {
                throw new \RuntimeException('Exploration event config not found for event ' . PlanetSectorEvent::NOTHING_TO_REPORT);
            }

            $planetSectorEvent = new PlanetSectorEvent(
                planetSector: $landingSector,
                config: $eventConfig,
            );
            $planetSectorEvent->addTag('always_successful_thanks_to_pilot');
            $this->eventService->callEvent($planetSectorEvent, PlanetSectorEvent::PLANET_SECTOR_EVENT);
        } else {
            $eventKey = $this->drawPlanetSectorEvent($landingSector);
            $eventConfig = $this->findPlanetSectorEventConfigByName($eventKey);
            if (!$eventConfig) {
                throw new \RuntimeException('Exploration event config not found for event ' . $eventKey);
            }

            $planetSectorEvent = new PlanetSectorEvent(
                planetSector: $landingSector,
                config: $eventConfig,
            );
            $this->eventService->callEvent($planetSectorEvent, PlanetSectorEvent::PLANET_SECTOR_EVENT);
        }

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

        $eventKey = $this->drawPlanetSectorEvent($sector);
        $eventConfig = $this->findPlanetSectorEventConfigByName($eventKey);
        // @TODO : remove this debug condition when all events are implemented
        if ($eventConfig === null) {
            $eventConfig = $this->findPlanetSectorEventConfigByName(PlanetSectorEvent::NOTHING_TO_REPORT);
            if ($eventConfig === null) {
                throw new \RuntimeException('Exploration event config not found for event ' . $eventKey);
            }
        }

        $event = new PlanetSectorEvent(
            planetSector: $sector,
            config: $eventConfig,
        );
        $this->eventService->callEvent($event, PlanetSectorEvent::PLANET_SECTOR_EVENT);

        return $exploration;
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    private function drawPlanetSectorEvent(PlanetSector $sector): string
    {
        $eventKey = $this->randomService->getSingleRandomElementFromProbaCollection($sector->getExplorationEvents());
        if (!is_string($eventKey)) {
            throw new \RuntimeException('Exploration event name should be a string');
        }

        return $eventKey;
    }

    private function findPlanetSectorConfigBySectorName(string $sectorName): PlanetSectorConfig
    {
        $planetSector = $this->entityManager->getRepository(PlanetSectorConfig::class)->findOneBySectorName($sectorName);
        if ($planetSector === null) {
            throw new \RuntimeException('PlanetSectorConfig not found for sector ' . $sectorName);
        }

        return $planetSector;
    }

    private function findPlanetSectorEventConfigByName(string $eventKey): ?PlanetSectorEventConfig
    {
        return $this->entityManager->getRepository(PlanetSectorEventConfig::class)->findOneByName($eventKey);
    }

    private function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }
}
