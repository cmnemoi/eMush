<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
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
        TranslationServiceInterface $translationService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
        $this->statusService = $statusService;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $lootedQuantity = (int) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());

        $exploration = $event->getExploration();
        if ($event->getName() === PlanetSectorEvent::FUEL && $exploration->hasAFunctionalDrill()) {
            $lootedQuantity *= 2;
        }

        $finder = $this->randomService->getRandomPlayer($exploration->getNotLostActiveExplorators());

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

        $logParameters = $this->getLogParameters($event);
        $logParameters['quantity'] = $lootedQuantity;
        $logParameters[$finder->getLogKey()] = $finder->getLogName();
        $logParameters['has_drill'] = $exploration->hasAFunctionalDrill() ? 'true' : 'false';

        return $this->createExplorationLog($event, $logParameters);
    }
}
