<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;

final class Back extends AbstractPlanetSectorEventHandler
{
    private ExplorationServiceInterface $explorationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        ExplorationServiceInterface $explorationService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->explorationService = $explorationService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::BACK;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $this->explorationService->closeExploration($event->getExploration(), $event->getTags());

        return $this->createExplorationLog($event);
    }
}
