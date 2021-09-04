<?php

namespace Mush\Status\Listener;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Service\ChargeStrategyServiceInterface;
use Mush\Status\Service\StatusCycleHandlerService;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusCycleSubscriber implements EventSubscriberInterface
{
    private ChargeStrategyServiceInterface $chargeStrategyService;
    private StatusCycleHandlerService $cycleHandlerService;

    public function __construct(
        ChargeStrategyServiceInterface $chargeStrategy,
        StatusCycleHandlerService $cycleHandlerService,
    ) {
        $this->chargeStrategyService = $chargeStrategy;
        $this->cycleHandlerService = $cycleHandlerService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusCycleEvent::STATUS_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(StatusCycleEvent $event): void
    {
        if (!($status = $event->getStatus())) {
            return;
        }

        if ($status instanceof ChargeStatus && ($strategyName = $status->getStrategy())) {
            if ($strategy = $this->chargeStrategyService->getStrategy($strategyName)) {
                $strategy->execute($status, $event->getReason());
            }
        }

        if ($cycleHandler = $this->cycleHandlerService->getStatusCycleHandler($status)) {
            $cycleHandler->handleNewCycle($status, $event->getHolder(), $event->getTime());
        }
    }
}
