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

final class FindLost extends AbstractPlanetSectorEventHandler
{
    private const NUMBER_OF_DESCRIPTIONS = 2;
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
        return PlanetSectorEvent::FIND_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $foundPlayer = $this->randomService->getRandomPlayer($event->getExploration()->getLostExplorators());

        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $foundPlayer,
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PRIVATE
        );

        // Remove lost planet sector after usage because it's not a "true" sector
        // (we don't want players to see it in astro terminal for example)
        $this->entityManager->remove($event->getPlanetSector());

        $logParameters = [
            $foundPlayer->getLogKey() => $foundPlayer->getLogName(),
            'version' => $this->randomService->random(1, self::NUMBER_OF_DESCRIPTIONS),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
