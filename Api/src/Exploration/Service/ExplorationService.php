<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

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
        $exploration->getClosedExploration()->setExploratorNames($players->map(fn (Player $player) => $player->getLogName())->toArray());
        $exploration->setNumberOfSectionsToVisit(min($numberOfSectorsToVisit, $planet->getSectors()->count()));

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

        foreach ($exploration->getExplorators() as $explorator) {
            $explorator->setExploration(null);
        }
        $exploration->getPlanet()->setExploration(null);

        // @TODO remove this debug line
        $exploration->getPlanet()->getSectors()->map(fn (PlanetSector $sector) => $sector->unvisit());

        $explorationEvent = new ExplorationEvent(
            exploration: $exploration,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_FINISHED);

        $this->delete([$exploration]);
        if (in_array(ExplorationEvent::ALL_EXPLORATORS_STUCKED, $reasons)) {
            $this->delete([$closedExploration]);
        }
    }

    public function computeExplorationEvents(Exploration $exploration): Exploration
    {
        $planet = $exploration->getPlanet();

        // add Landing planet sector at the beginning of the exploration
        $landingSectorConfig = $this->findPlanetSectorConfigBySectorName(PlanetSectorEnum::LANDING);
        $landingSector = new PlanetSector($landingSectorConfig, $planet);

        $eventName = $this->drawPlanetSectorEvent($landingSector);
        $config = $this->findPlanetSectorEventConfigByName($eventName);
        if (!$config) {
            throw new \RuntimeException('Exploration event config not found for event name ' . $eventName);
        }

        $planetSectorEvent = new PlanetSectorEvent(
            planetSector: $landingSector,
            config: $config,
        );
        $this->eventService->callEvent($planetSectorEvent, $eventName);

        for ($i = 0; $i < $exploration->getNumberOfSectionsToVisit(); ++$i) {
            /** @var PlanetSector $sector */
            $sector = $this->randomService->getRandomPlanetSectorsToVisit($planet, 1)->first();
            $sector->visit();
            $exploration->getClosedExploration()->addExploredSectorKey($sector->getName());

            $eventName = $this->drawPlanetSectorEvent($sector);
            $config = $this->findPlanetSectorEventConfigByName($eventName);
            // @TODO : remove this debug condition when all events are implemented
            if ($config === null) {
                $config = $this->findPlanetSectorEventConfigByName(PlanetSectorEvent::NOTHING_TO_REPORT);
                if ($config === null) {
                    throw new \RuntimeException('Exploration event config not found for event name ' . $eventName);
                }
            }

            $event = new PlanetSectorEvent(
                planetSector: $sector,
                config: $config,
            );
            $this->eventService->callEvent($event, $eventName);
        }

        $this->persist([$exploration]);

        return $exploration;
    }

    public function createExplorationLog(PlanetSectorEvent $event, array $parameters = []): ExplorationLog
    {
        $exploration = $event->getExploration();
        $eventName = $event->getEventName();
        $sector = $event->getPlanetSector();

        $closedExploration = $exploration->getClosedExploration();

        $explorationLog = new ExplorationLog($closedExploration);
        $explorationLog->setPlanetSectorName($sector->getName());
        $explorationLog->setEventName($eventName);
        $explorationLog->setParameters($parameters);

        $closedExploration->addLog($explorationLog);

        $this->persist([$explorationLog]);

        return $explorationLog;
    }

    public function removeHealthToAllExplorators(PlanetSectorEvent $event): array
    {
        $exploration = $event->getExploration();

        // also remove health to explorators in stucked in the ship for landign events
        $explorators = $event->getPlanetSector()->getName() === PlanetSectorEnum::LANDING ?
            $exploration->getExplorators() :
            $exploration->getActiveExplorators();

        $healthLost = $this->drawEventOutputQuantity($event->getOutputQuantityTable());
        foreach ($explorators as $explorator) {
            $playerVariableEvent = new PlayerVariableEvent(
                player: $explorator,
                variableName: PlayerVariableEnum::HEALTH_POINT,
                quantity: -$healthLost,
                tags: $event->getTags(),
                time: new \DateTime()
            );
            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        return array_merge([
            'quantity' => $healthLost,
        ], $event->getLogParameters());
    }

    public function removeHealthToARandomExplorator(PlanetSectorEvent $event): array
    {
        $exploration = $event->getExploration();

        // also remove health to explorators in stucked in the ship for landign events
        $explorators = $event->getPlanetSector()->getName() === PlanetSectorEnum::LANDING ?
            $exploration->getExplorators() :
            $exploration->getActiveExplorators();

        $exploratorToInjure = $this->randomService->getRandomPlayer($explorators);
        $healthLost = $this->drawEventOutputQuantity($event->getOutputQuantityTable());

        $playerVariableEvent = new PlayerVariableEvent(
            player: $exploratorToInjure,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$healthLost,
            tags: $event->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        return array_merge([
            $exploratorToInjure->getLogKey() => $exploratorToInjure->getLogName(),
            'quantity' => $healthLost,
        ], $event->getLogParameters());
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    private function drawEventOutputQuantity(?ProbaCollection $outputQuantityTable): int
    {
        if ($outputQuantityTable === null) {
            throw new \RuntimeException('You need an output quantity table to draw an event output quantity');
        }

        $quantity = $this->randomService->getSingleRandomElementFromProbaCollection($outputQuantityTable);
        if (!is_int($quantity)) {
            throw new \RuntimeException('Quantity should be an int');
        }

        return $quantity;
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

    private function findPlanetSectorEventConfigByName(string $eventName): ?PlanetSectorEventConfig
    {
        return $this->entityManager->getRepository(PlanetSectorEventConfig::class)->findOneByEventName($eventName);
    }

    private function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }
}
