<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Service\ModifierRequirementService;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private ModifierRequirementService $modifierActivationRequirementService;

    public function __construct(
        EventServiceInterface $eventService,
        ModifierRequirementService $modifierActivationRequirementService,
    ) {
        $this->eventService = $eventService;
        $this->modifierActivationRequirementService = $modifierActivationRequirementService;
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
        $cycleModifiers = $this->modifierActivationRequirementService->getActiveModifiers($cycleModifiers, [EventEnum::NEW_CYCLE], $holder);

        /** @var GameModifier $modifier */
        foreach ($cycleModifiers as $modifier) {
            $this->createQuantityEvent($holder, $modifier, $event->getTime(), $event->getTags());
        }
    }

    public function onNewDay(AbstractGameEvent $event): void
    {
        $holder = $this->getModifierHolder($event);

        $cycleModifiers = $holder->getModifiers()->getScopedModifiers([EventEnum::NEW_DAY]);
        $cycleModifiers = $this->modifierActivationRequirementService->getActiveModifiers($cycleModifiers, [EventEnum::NEW_CYCLE], $holder);

        /** @var GameModifier $modifier */
        foreach ($cycleModifiers as $modifier) {
            $this->createQuantityEvent($holder, $modifier, $event->getTime(), $event->getTags());
        }
    }

    public function onAction(ActionEvent $event): void
    {
        $holder = $event->getPlayer();

        $modifiers = $holder->getModifiers()->getScopedModifiers([ActionEvent::POST_ACTION]);
        $modifiers = $this->modifierActivationRequirementService->getActiveModifiers($modifiers, $event->getTags(), $holder);

        /** @var GameModifier $modifier */
        foreach ($modifiers as $modifier) {
            $this->createQuantityEvent($holder, $modifier, $event->getTime(), $event->getTags());
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

    private function createQuantityEvent(ModifierHolder $holder, GameModifier $modifier, \DateTime $time, array $reasons): void
    {
        $modifierConfig = $modifier->getModifierConfig();

        if ($modifierConfig instanceof TriggerEventModifierConfig &&
            ($target = $modifierConfig->getModifiedVariable()) !== null
        ) {
            $value = $modifierConfig->getQuantity();

            if (($modifierName = $modifierConfig->getModifierName()) !== null) {
                $reasons[] = $modifierName;
            }

            if ($holder instanceof Player) {
                $event = new PlayerVariableEvent(
                    $holder,
                    $target,
                    $value,
                    $reasons,
                    $time,
                );
                $event->setVisibility($modifierConfig->getVisibility());
                $this->eventService->callEvent($event, QuantityEventInterface::CHANGE_VARIABLE);
            }

            if ($holder instanceof Daedalus) {
                $event = new DaedalusVariableEvent(
                    $holder,
                    $target,
                    $value,
                    $reasons,
                    $time,
                );
                $this->eventService->callEvent($event, QuantityEventInterface::CHANGE_VARIABLE);
            }
        }
    }
}
