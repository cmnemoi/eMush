<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractCreateLootStatus extends AbstractPlanetSectorEventHandler
{
    protected StatusServiceInterface $statusService;

    protected static array $eventStatusMap = [
        PlanetSectorEvent::FUEL => DaedalusStatusEnum::EXPLORATION_FUEL,
        PlanetSectorEvent::OXYGEN => DaedalusStatusEnum::EXPLORATION_OXYGEN,
    ];

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->statusService = $statusService;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $lootedQuantity = (int) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());
        $finder = $this->randomService->getRandomPlayer($event->getExploration()->getNotLostActiveExplorators());

        /** @var ChargeStatus $lootStatus */
        $lootStatus = $this->statusService->createStatusFromName(
            statusName: self::$eventStatusMap[$event->getName()],
            holder: $event->getExploration()->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );

        $this->statusService->updateCharge(
            chargeStatus: $lootStatus,
            delta: $lootedQuantity,
            tags: $event->getTags(),
            time: $event->getTime(),
        );

        $logParameters = [
            'quantity' => $lootedQuantity,
            $finder->getLogKey() => $finder->getLogName(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
