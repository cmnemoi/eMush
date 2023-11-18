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
        $explorators = $exploration->getExplorators();
        $planet = $exploration->getPlanet();

        $explorationEvent = new ExplorationEvent(
            exploration: $exploration,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($explorationEvent, ExplorationEvent::EXPLORATION_FINISHED);

        foreach ($explorators as $explorator) {
            $explorator->setExploration(null);
        }
        $planet->setExploration(null);

        $closedExploration->finishExploration();

        $this->delete([$exploration]);
        if (in_array(ExplorationEvent::ALL_EXPLORATORS_STUCKED, $reasons)) {
            $this->delete([$closedExploration]);
        }
    }

    public function createExplorationLog(PlanetSectorEvent $event, array $parameters = []): ExplorationLog
    {
        $closedExploration = $event->getExploration()->getClosedExploration();

        $explorationLog = new ExplorationLog($closedExploration);
        $explorationLog->setPlanetSectorName($event->getPlanetSector()->getName());
        $explorationLog->setEventName($event->getEventName());
        $explorationLog->setParameters(array_merge($event->getLogParameters(), $parameters));

        $closedExploration->addLog($explorationLog);

        $this->persist([$explorationLog]);

        return $explorationLog;
    }

    public function dispatchExplorationEvent(Exploration $exploration): Exploration
    {
        $closedExploration = $exploration->getClosedExploration();
        $planet = $exploration->getPlanet();

        // if exploration is just starting, dispatch landing event
        if ($exploration->getCycle() === 0) {
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
        } else {
            /** @var PlanetSector $sector */
            $sector = $this->randomService->getRandomPlanetSectorsToVisit($planet, 1)->first();
            $sector->visit();
            $closedExploration->addExploredSectorKey($sector->getName());

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

        $exploration->setCycle($exploration->getCycle() + 1);
        $this->persist([$exploration]);

        // close exploration prematurely if needed
        $allActiveExploratorsAreDead = $exploration->getActiveExplorators()->isEmpty();
        $allSectorsVisited = $exploration->getCycle() >= $exploration->getNumberOfSectionsToVisit() + 1;

        if ($allActiveExploratorsAreDead) {
            $this->closeExploration($exploration, [ExplorationEvent::ALL_EXPLORATORS_ARE_DEAD]);
        } elseif ($allSectorsVisited) {
            $this->closeExploration($exploration, [ExplorationEvent::ALL_SECTORS_VISITED]);
        }

        return $exploration;
    }

    public function removeHealthToAllExplorators(PlanetSectorEvent $event): array
    {
        $exploration = $event->getExploration();

        // also remove health to explorators stucked in the ship for landing events
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

        // also remove health to explorators stucked in the ship for landing events
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
