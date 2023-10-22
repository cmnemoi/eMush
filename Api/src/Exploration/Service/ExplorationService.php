<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\ExplorationPlanetSectorEventConfig;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\ExplorationPlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;

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

    public function createExploration(PlayerCollection $players, array $reasons): Exploration
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

        $this->persist([$exploration]);

        $explorationEvent = new ExplorationEvent(
            exploration: $exploration,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_STARTED);

        return $exploration;
    }

    public function closeExploration(Exploration $exploration, array $reasons): ClosedExploration
    {
        $closedExploration = $exploration->getClosedExploration();

        foreach ($exploration->getExplorators() as $explorator) {
            $explorator->setExploration(null);
        }
        $exploration->getPlanet()->setExploration(null);

        // @TODO remove this debug line
        $exploration->getPlanet()->getSectors()->map(fn (PlanetSector $sector) => $sector->unvisit());

        $this->delete([$exploration]);

        $explorationEvent = new ExplorationEvent(
            exploration: $exploration,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_FINISHED);

        return $closedExploration;
    }

    public function computeExplorationEvents(Exploration $exploration): Exploration
    {
        $planet = $exploration->getPlanet();
        $sectorsToVisit = $planet->getUnvisitedSectors();

        $eventLogs = [];

        // add Landing planet sector at the beginning of the exploration
        $landingSectorConfig = $this->findPlanetSectorConfigBySectorName(PlanetSectorEnum::LANDING);
        $landingSector = new PlanetSector($landingSectorConfig, $planet);

        $eventName = $this->drawPlanetSectorEvent($landingSector);
        $config = $this->findPlanetSectorEventConfigByName($eventName);
        if (!$config) {
            throw new \RuntimeException('Exploration event config not found for event name ' . $eventName);
        }

        $planetSectorEvent = new ExplorationPlanetSectorEvent(
            planetSector: $landingSector,
            config: $config,
        );
        $this->eventService->callEvent($planetSectorEvent, $eventName);

        // @TODO : select randomly a sector to visit given their `weightAtExploration` property
        // @TODO : add a limit to the number of sectors to visit per exploration
        foreach ($sectorsToVisit as $sector) {
            $sector->visit();

            $eventName = $this->drawPlanetSectorEvent($sector);
            $config = $this->findPlanetSectorEventConfigByName($eventName);
            // @TODO : remove this debug condition when all events are implemented
            if ($config === null) {
                $config = $this->findPlanetSectorEventConfigByName(ExplorationPlanetSectorEvent::NOTHING_TO_REPORT);
                if ($config === null) {
                    throw new \RuntimeException('Exploration event config not found for event name ' . $eventName);
                }
            }

            $event = new ExplorationPlanetSectorEvent(
                planetSector: $sector,
                config: $config,
            );
            $this->eventService->callEvent($event, $eventName);
        }

        $this->persist(array_merge($eventLogs, [$exploration]));

        return $exploration;
    }

    public function createExplorationLog(ExplorationPlanetSectorEvent $event, array $parameters = []): ExplorationLog
    {
        $exploration = $event->getExploration();
        $eventName = $event->getEventName();
        $sector = $event->getPlanetSector();

        $closedExploration = $exploration->getClosedExploration();

        $explorationLog = new ExplorationLog($closedExploration);
        $explorationLog->setPlanetSectorName($sector->getName());
        $explorationLog->setEventName($eventName);
        $explorationLog->setEventDescription($eventName);
        $explorationLog->setParameters($parameters);
        $explorationLog->addParameter('planet', $exploration->getPlanet()->getName()->toArray());

        $closedExploration->addLog($explorationLog);

        $this->persist([$explorationLog]);

        return $explorationLog;
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
        $eventName = $this->randomService->getSingleRandomElementFromProbaCollection($sector->getExplorationEvents());
        if (!is_string($eventName)) {
            throw new \RuntimeException('Exploration event name should be a string');
        }

        return $eventName;
    }

    private function findPlanetSectorConfigBySectorName(string $sectorName): PlanetSectorConfig
    {
        $planetSector = $this->entityManager->getRepository(PlanetSectorConfig::class)->findOneBySectorName($sectorName);
        if ($planetSector === null) {
            throw new \RuntimeException('PlanetSectorConfig not found for sector name ' . $sectorName);
        }

        return $planetSector;
    }

    private function findPlanetSectorEventConfigByName(string $eventName): ?ExplorationPlanetSectorEventConfig
    {
        return $this->entityManager->getRepository(ExplorationPlanetSectorEventConfig::class)->findOneByEventName($eventName);
    }

    private function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }
}
