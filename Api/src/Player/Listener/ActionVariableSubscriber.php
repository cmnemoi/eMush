<?php

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionVariableSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public const ACTION_CLUMSINESS_DAMAGE = -2;

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
            ActionVariableEvent::ROLL_ACTION_PERCENTAGE => 'onRollPercentage',
        ];
    }

    public function onApplyCost(ActionVariableEvent $event): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            $event->getVariableName(),
            -$event->getRoundedQuantity(),
            $event->getTags(),
            $event->getTime()
        );
        $playerVariableEvent->setVisibility(VisibilityEnum::HIDDEN);

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    public function onRollPercentage(ActionVariableEvent $event): void
    {
        if ($event->getVariableName() === ActionVariableEnum::PERCENTAGE_INJURY) {
            $isHurt = $this->randomService->isSuccessful($event->getRoundedQuantity());

            $tags = $event->getTags();
            $tags[] = EndCauseEnum::CLUMSINESS;

            if ($isHurt) {
                $playerVariableEvent = new PlayerVariableEvent(
                    $event->getAuthor(),
                    PlayerVariableEnum::HEALTH_POINT,
                    self::ACTION_CLUMSINESS_DAMAGE,
                    $tags,
                    $event->getTime()
                );

                $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
            }
        }
    }
}
