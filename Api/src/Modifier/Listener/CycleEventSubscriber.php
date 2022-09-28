<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Service\ModifierConditionService;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleEventSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private ModifierConditionService $modifierConditionService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ModifierConditionService $modifierConditionService,
    ) {
          $this->eventService = $eventDispatcher;
        $this->modifierConditionService = $modifierConditionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewDay',
            PlaceCycleEvent::PLACE_NEW_CYCLE => 'onNewCycle',
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => 'onNewCycle',
            PlayerCycleEvent::PLAYER_NEW_DAY => 'onNewDay',
            DaedalusCycleEvent::DAEDALUS_NEW_DAY => 'onNewDay',
            PlaceCycleEvent::PLACE_NEW_DAY => 'onNewDay',
            EquipmentCycleEvent::EQUIPMENT_NEW_DAY => 'onNewDay',
            ActionEvent::POST_ACTION => 'onAction',
        ];
    }

    public function onNewCycle(AbstractGameEvent $event): void
    {
        $holder = $this->getModifierHolder($event);

        $cycleModifiers = $holder->getModifiers()->getScopedModifiers([EventEnum::NEW_CYCLE]);
        $cycleModifiers = $this->modifierConditionService->getActiveModifiers($cycleModifiers, EventEnum::NEW_CYCLE, $holder);
        $cycleModifiers = $cycleModifiers->sortModifiersByDelta(false);

        /** @var Modifier $modifier */
        foreach ($cycleModifiers as $modifier) {
            $event = $this->createQuantityEvent($holder, $modifier, $event->getTime(), $event->getReason());

            $this->eventService->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }

    public function onNewDay(AbstractGameEvent $event): void
    {
        $holder = $this->getModifierHolder($event);

        $cycleModifiers = $holder->getModifiers()->getScopedModifiers([EventEnum::NEW_DAY]);
        $cycleModifiers = $this->modifierConditionService->getActiveModifiers($cycleModifiers, EventEnum::NEW_CYCLE, $holder);
        $cycleModifiers = $cycleModifiers->sortModifiersByDelta(false);

        /** @var Modifier $modifier */
        foreach ($cycleModifiers as $modifier) {
            $event = $this->createQuantityEvent($holder, $modifier, $event->getTime(), $event->getReason());

            $this->eventService->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }

    public function onAction(ActionEvent $event): void
    {
        $holder = $event->getPlayer();

        $modifiers = $holder->getModifiers()->getScopedModifiers([ActionEvent::POST_ACTION]);
        $modifiers = $this->modifierConditionService->getActiveModifiers($modifiers, $event->getReason(), $holder);
        $modifiers = $modifiers->sortModifiersByDelta(false);

        /** @var Modifier $modifier */
        foreach ($modifiers as $modifier) {
            $event = $this->createQuantityEvent($holder, $modifier, $event->getTime(), $event->getReason());

            $this->eventService->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }

    private function getModifierHolder(AbstractGameEvent $event): ModifierHolder
    {
        switch (true) {
            case $event instanceof PlayerCycleEvent:
                return $event->getPlayer();
            case $event instanceof DaedalusCycleEvent:
                return $event->getDaedalus();
            case $event instanceof EquipmentCycleEvent:
                return $event->getGameEquipment();
            case $event instanceof PlaceCycleEvent:
                return $event->getPlace();
            default:
                throw new \LogicException('Unexpected event type');
        }
    }

    private function createQuantityEvent(ModifierHolder $holder, Modifier $modifier, \DateTime $time, string $eventReason): AbstractQuantityEvent
    {
        $modifierConfig = $modifier->getModifierConfig();

        $target = $modifierConfig->getTarget();
        $value = intval($modifierConfig->getDelta());
        $reason = $modifierConfig->getName() ?: $eventReason;

        switch (true) {
            case $holder instanceof Player:
                return new PlayerVariableEvent(
                    $holder,
                    $target,
                    $value,
                    $reason,
                    $time,
                );

            case $holder instanceof Daedalus:
                return new DaedalusModifierEvent(
                    $holder,
                    $target,
                    $value,
                    $reason,
                    $time,
                );
            default:
                throw new \LogicException('Unexpected modifier holder type');
        }
    }
}
