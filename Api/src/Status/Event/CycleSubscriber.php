<?php

namespace Mush\Status\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Service\ChargeStrategyServiceInterface;
use Mush\Status\Service\StatusCycleHandlerService;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    /**
     * @return void
     */
    public function onNewCycle(CycleEvent $event)
    {
        if (!($status = $event->getStatus())) {
            return;
        }

        if ($status instanceof ChargeStatus && ($strategyName = $status->getStrategy())) {
            if ($strategy = $this->chargeStrategyService->getStrategy($strategyName)) {
                $strategy->execute($status);
            }
        }

        if ($cycleHandler = $this->cycleHandlerService->getStatusCycleHandler($status)) {
            $cycleHandler->handleNewCycle($status, $event->getDaedalus(), $event->getTime());
        }
    }
}
