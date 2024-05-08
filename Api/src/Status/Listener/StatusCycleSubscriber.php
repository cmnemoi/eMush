<?php

namespace Mush\Status\Listener;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Service\ChargeStrategyServiceInterface;
use Mush\Status\Service\StatusCycleHandlerService;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusCycleSubscriber implements EventSubscriberInterface
{
    private ChargeStrategyServiceInterface $chargeStrategyService;
    private StatusCycleHandlerService $cycleHandlerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        ChargeStrategyServiceInterface $chargeStrategy,
        StatusCycleHandlerService $cycleHandlerService,
        StatusServiceInterface $statusService
    ) {
        $this->chargeStrategyService = $chargeStrategy;
        $this->cycleHandlerService = $cycleHandlerService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusCycleEvent::STATUS_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(StatusCycleEvent $event): void
    {
        $status = $event->getStatus();

        // Update statuses charges
        if ($status instanceof ChargeStatus && ($strategyName = $status->getStrategy())) {
            if ($strategy = $this->chargeStrategyService->getStrategy($strategyName)) {
                $strategy->execute($status, $event->getTags(), $event->getTime());
            }
        }

        // Apply statuses effects
        if ($cycleHandler = $this->cycleHandlerService->getStatusCycleHandler($status)) {
            $cycleHandler->handleNewCycle($status, $event->getHolder(), $event->getTime());
        }

        // Reset number of fires killed by auto watering
        if ($status->getName() === DaedalusStatusEnum::AUTO_WATERING_KILLED_FIRES) {
            /** @var ChargeStatus $autoWateringStatus */
            $autoWateringStatus = $status;
            $this->statusService->updateCharge(
                $autoWateringStatus,
                -$autoWateringStatus->getCharge(),
                $event->getTags(),
                $event->getTime()
            );
        }
    }
}
