<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\ExplorationEvent;
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
        $closedExploration = $exploration->getClosedExploration();
        $planet = $exploration->getPlanet();
        $sectors = $planet->getSectors();

        $eventLogs = [];

        // @TODO : select randomly a sector to visit given their `weightAtExploration` property
        // @TODO : add a limit to the number of sectors to visit per exploration
        // @TODO : add Landing planet sector at the beginning of the exploration
        foreach ($sectors as $sector) {
            $sector->visit();

            $eventName = $this->randomService->getSingleRandomElementFromProbaCollection($sector->getExplorationEvents());
            if (!is_string($eventName)) {
                throw new \RuntimeException('Exploration event name should be a string');
            }

            $explorationLog = new ExplorationLog($closedExploration);
            $explorationLog->setPlanetSectorName($sector->getName());
            $explorationLog->setEventName($eventName);

            $closedExploration->addLog($explorationLog);

            $eventLogs[] = $explorationLog;
        }

        $this->persist(array_merge($eventLogs, [$exploration]));

        return $exploration;
    }

    private function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
