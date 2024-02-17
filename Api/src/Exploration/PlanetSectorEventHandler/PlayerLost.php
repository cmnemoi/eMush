<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PlayerLost extends AbstractPlanetSectorEventHandler
{
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->statusService = $statusService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::PLAYER_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $lostPlayer = $this->randomService->getRandomPlayer($event->getExploration()->getNotLostExplorators());

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $lostPlayer,
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PRIVATE,
        );

        return $this->createExplorationLog($event, parameters: [$lostPlayer->getLogKey() => $lostPlayer->getLogName()]);
    }
}
