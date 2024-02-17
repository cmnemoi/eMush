<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;

abstract class AbstractPlanetSectorEventHandler
{
    protected EntityManagerInterface $entityManager;
    protected EventServiceInterface $eventService;
    protected RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    abstract public function getName(): string;

    abstract public function handle(PlanetSectorEvent $event): ExplorationLog;

    protected function createExplorationLog(PlanetSectorEvent $event, array $parameters = []): ExplorationLog
    {
        $closedExploration = $event->getExploration()->getClosedExploration();

        $explorationLog = new ExplorationLog($closedExploration);
        $explorationLog->setPlanetSectorName($event->getPlanetSector()->getName());
        $explorationLog->setEventName($event->getName());
        $explorationLog->setParameters(array_merge($event->getLogParameters(), $parameters));

        $closedExploration->addLog($explorationLog);

        $this->entityManager->persist($explorationLog);

        return $explorationLog;
    }

    protected function drawEventOutputQuantity(?ProbaCollection $outputTable): int
    {
        if ($outputTable === null) {
            throw new \RuntimeException('You need an output quantity table to draw an event output quantity');
        }

        $quantity = $this->randomService->getSingleRandomElementFromProbaCollection($outputTable);
        if (!is_int($quantity)) {
            throw new \RuntimeException('Quantity should be an int');
        }

        return $quantity;
    }
}
