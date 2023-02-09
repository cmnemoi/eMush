<?php

namespace Mush\Game\Service;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
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

        $triggerModifiers = $modifiers->getTriggerEventModifiers();

        if ($dispatch === true) {
            $this->applyTriggerModifiers($triggerModifiers, $event);
        }

        $event = $this->applyVariableModifiers($modifiers, $event, $dispatch);


        return $event;
    }

    private function applyTriggerModifiers(ModifierCollection $triggerModifiers, AbstractGameEvent $event): void
    {
        foreach ($triggerModifiers as $modifier) {
            /** @var TriggerEventModifierConfig $modifierConfig */
            $modifierConfig = $modifier->getModifierConfig();
            $triggeredEvent = $this->modifierService->createTriggeredEvent($modifierConfig, $event);
            $this->callEvent($triggeredEvent, $triggeredEvent->getEventName());
            $modifierEvent = $this->modifierService->createModifierEvent($modifier, $event->getTags(), $event->getTime());
            $this->callEvent($modifierEvent, ModifierEvent::APPLY_MODIFIER);
        }
    }

    private function applyVariableModifiers(ModifierCollection $modifiers, AbstractGameEvent $event, bool $dispatch = true): AbstractGameEvent
    {
        if (!($event instanceof VariableEventInterface)) {
            throw new \Error('variableEventModifiers only apply on quantityEventInterface');
        }

        $variableModifiers = $modifiers->getVariableEventModifiers($event->getVariableName());

        $event = $this->modifierService->modifyVariableEvent($variableModifiers, $event);

        if ($dispatch) {
            foreach ($variableModifiers as $modifier) {
                $modifierEvent = $this->modifierService->createModifierEvent($modifier, $event->getTags(), $event->getTime());
                $this->callEvent($modifierEvent, ModifierEvent::APPLY_MODIFIER);
            }
        }

        return $event;
    }

    public function previewEvent(AbstractGameEvent $event, string $name): AbstractGameEvent
    {
        $event->setEventName($name);
        return $this->applyModifiers($event, false);
    }
}
