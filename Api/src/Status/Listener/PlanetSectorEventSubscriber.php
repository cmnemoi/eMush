<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    private ExplorationServiceInterface $explorationService;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;

    public function __construct(
        ExplorationServiceInterface $explorationService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
    ) {
        $this->explorationService = $explorationService;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlanetSectorEvent::OXYGEN => 'onOxygen',
        ];
    }

    public function onOxygen(PlanetSectorEvent $event): void
    {
        $table = $event->getOutputQuantityTable();
        if (!$table) {
            throw new \Exception('Oxygen planet sector event must have an output quantity table');
        }

        $lootedOxygen = (int) $this->randomService->getSingleRandomElementFromProbaCollection($table);
        $logParameters = ['quantity' => $lootedOxygen];

        /** @var ChargeStatus $oxygenStatus */
        $oxygenStatus = $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EXPLORATION_OXYGEN,
            holder: $event->getExploration()->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );

        $this->statusService->updateCharge(
            chargeStatus: $oxygenStatus,
            delta: $lootedOxygen,
            tags: $event->getTags(),
            time: $event->getTime(),
        );

        $this->explorationService->createExplorationLog($event, $logParameters);
    }
}
