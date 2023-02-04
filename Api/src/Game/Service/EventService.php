<?php

namespace Mush\Game\Service;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Event\ModifiableEventInterface;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Modifier\Service\ModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventService implements EventServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private ModifierServiceInterface $modifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ModifierServiceInterface $modifierService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
    }

    public function callEvent(AbstractGameEvent $event, string $name, AbstractGameEvent $caller = null): void
    {
        if ($caller !== null) {
            $event->setTags(array_merge(
                $event->getTags(),
                array_merge($caller->getTags())
            ));
        }
        $event->setEventName($name);


        $event = $this->applyModifiers($event);



        $this->eventDispatcher->dispatch($event, $name);
    }

    private function applyModifiers(AbstractGameEvent $event, bool $dispatch = true): AbstractGameEvent
    {
        if (!($event instanceof ModifiableEventInterface)) {
            return $event;
        }

        $modifiers = $event->getModifiers()->getModifiersByEvent($event->getEventName());

        $modifiers = $this->modifierService->getActiveModifiers($modifiers, $event->getTags());

        foreach ($modifiers as $modifier) {
            $event = $this->applyModifier($modifier, $event, $dispatch);
        }


        return $event;
    }

    private function applyModifier(GameModifier $modifier, AbstractGameEvent $event, bool $dispatch = true): AbstractGameEvent
    {
        $modifierConfig = $modifier->getModifierConfig();

        if ($dispatch && $modifierConfig instanceof TriggerEventModifierConfig) {
            $triggeredEvent = $this->modifierService->createTriggeredEvent($modifierConfig);

            $this->callEvent($triggeredEvent, $triggeredEvent->getName());
        } else if ($modifierConfig instanceof VariableEventModifierConfig) {
            if (!($event instanceof QuantityEventInterface)) {
                throw new \Error('variableEventModifiers only apply on quantityEventInterface');
            }

            $event = $this->modifierService->updateEvent($modifierConfig, $event);
        }

        if ($dispatch) {
            $modifierEvent = $this->modifierService->createModifierEvent($modifier, $event->getTags(), $event->getTime());
            $this->callEvent($modifierEvent, ModifierEvent::APPLY_MODIFIER);
        }

        return $event;
    }

    public function previewEvent(AbstractGameEvent $event, string $name): AbstractGameEvent
    {
        $event->setEventName($name);
        return $this->applyModifiers($event, false);
    }
}
