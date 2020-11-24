<?php

namespace Mush\Status\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Service\ChargeStrategyServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private ChargeStrategyServiceInterface $chargeStrategyService;

    private StatusServiceInterface $statusService;

    public function __construct(
        ChargeStrategyServiceInterface $chargeStrategy,
        StatusServiceInterface $statusService
    ) {
        $this->chargeStrategyService = $chargeStrategy;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($status = $event->getStatus())) {
            return;
        }
        $strategy = null;
        if ($status instanceof ChargeStatus) {
            if ($strategy = $this->chargeStrategyService->getStrategy($status->getStrategy())) {
                $strategy->execute($status);
            }
        }
    }
}
