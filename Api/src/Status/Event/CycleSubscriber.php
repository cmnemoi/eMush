<?php

namespace Mush\Status\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Status\ChargeStrategies\CycleDecrease;
use Mush\Status\ChargeStrategies\CycleIncrease;
use Mush\Status\ChargeStrategies\PlantStrategy;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
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
            switch ($status->getStrategy()) {
                case ChargeStrategyTypeEnum::CYCLE_INCREMENT:
                    $strategy = new CycleIncrease();
                    break;
                case ChargeStrategyTypeEnum::CYCLE_DECREMENT:
                    $strategy = new CycleDecrease();
                    break;
                case ChargeStrategyTypeEnum::PLANT:
                    $strategy = new PlantStrategy();
                    break;
            }
        }

        if (null !== $strategy) {
            $strategy->apply($status);
        }
    }
}
