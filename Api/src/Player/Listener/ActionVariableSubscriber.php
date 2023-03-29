<?php

namespace Mush\Player\Listener;

use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionVariableSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public const ACTION_INJURY_MODIFIER = -2;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
            ActionVariableEvent::ROLL_PERCENTAGE_INJURY => 'onRollPercentageInjury',
        ];
    }

    public function onApplyCost(ActionVariableEvent $event): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            $event->getPlayer(),
            $event->getVariableName(),
            $event->getQuantity(),
            $event->getTags(),
            $event->getTime()
        );

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    public function onRollPercentageInjury(ActionVariableEvent $event): void
    {
        $isHurt = $this->randomService->isSuccessful($event->getQuantity());

        $tags = $event->getTags();
        $tags[] = EndCauseEnum::INJURY;

        if ($isHurt) {
            $playerVariableEvent = new PlayerVariableEvent(
                $event->getPlayer(),
                PlayerVariableEnum::HEALTH_POINT,
                self::ACTION_INJURY_MODIFIER,
                $tags,
                $event->getTime()
            );

            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
