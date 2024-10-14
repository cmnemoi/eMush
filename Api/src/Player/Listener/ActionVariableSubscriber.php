<?php

namespace Mush\Player\Listener;

use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionVariableSubscriber implements EventSubscriberInterface
{
    public const ACTION_CLUMSINESS_DAMAGE = -2;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private EventServiceInterface $eventService,
    ) {}

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
        if ($event->isNotAboutPercentageInjuryVariable()) {
            return;
        }

        $playerShouldBeHurt = $this->d100Roll->isSuccessful($event->getRoundedQuantity());
        if ($playerShouldBeHurt) {
            $this->hurtPlayer($event);
            $this->infectPlayer($event);
        }
    }

    private function hurtPlayer(ActionVariableEvent $event): void
    {
        $author = $event->getAuthor();
        $event->addTag(EndCauseEnum::CLUMSINESS);

        $playerVariableEvent = new PlayerVariableEvent(
            $author,
            PlayerVariableEnum::HEALTH_POINT,
            self::ACTION_CLUMSINESS_DAMAGE,
            $event->getTags(),
            $event->getTime()
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function infectPlayer(ActionVariableEvent $event): void
    {
        $author = $event->getAuthor();
        $pickedItem = $event->getItemActionTargetOrNull();

        if ($pickedItem?->doesNotHaveStatus(EquipmentStatusEnum::CAT_INFECTED) || $author->isMush()) {
            return;
        }

        $playerVariableEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            PlayerVariableEnum::SPORE,
            1,
            $event->getTagsWithout(EndCauseEnum::CLUMSINESS),
            $event->getTime()
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
