<?php

namespace Mush\Game\Service;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Modifier\Service\EventCreationServiceInterface;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventService implements EventServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private ModifierRequirementServiceInterface $modifierRequirementService;
    private EventModifierServiceInterface $modifierService;
    private EventCreationServiceInterface $eventCreationService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventModifierServiceInterface $modifierService,
        ModifierRequirementServiceInterface $modifierRequirementService,
        EventCreationServiceInterface $eventCreationService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
        $this->modifierRequirementService = $modifierRequirementService;
        $this->eventCreationService = $eventCreationService;
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

        if ($event !== null) {
            $this->eventDispatcher->dispatch($event, $event->getEventName());
        }
    }

    /**
     * @throws \Exception
     */
    private function applyModifiers(AbstractGameEvent $event, bool $dispatch = true): ?AbstractGameEvent
    {
        $modifiers = $event->getModifiers();
        $modifiers = $this->modifierRequirementService->getActiveModifiers($modifiers, $event->getTags());

        $replaceEventModifiers = $modifiers->getTriggerEventModifiersReplace();
        $event = $this->applyReplaceModifiers($replaceEventModifiers, $event, true);

        if ($event === null) {
            return null;
        }

        $triggerModifiers = $modifiers->getTriggerEventModifiersNoReplace();
        if ($dispatch === true) {
            $this->applyTriggerModifiers($triggerModifiers, $event);
        }

        $variableModifiers = $modifiers->getVariableEventModifiers();

        return $this->applyVariableModifiers($variableModifiers, $event, $dispatch);
    }

    /**
     * @throws \Exception
     */
    private function applyTriggerModifiers(ModifierCollection $triggerModifiers, AbstractGameEvent $event): void
    {
        foreach ($triggerModifiers as $modifier) {
            $tags = $event->getTags();
            $tags[] = $modifier->getModifierConfig()->getModifierName() ?: $modifier->getModifierConfig()->getName();

            /** @var TriggerEventModifierConfig $modifierConfig */
            $modifierConfig = $modifier->getModifierConfig();

            $eventConfig = $modifierConfig->getTriggeredEvent();

            if ($eventConfig !== null) {
                $triggeredEvents = $this->eventCreationService->createEvents(
                    $eventConfig,
                    $modifier->getModifierHolder(),
                    $event->getAuthor(),
                    $tags,
                    $event->getTime()
                );

                /** @var AbstractGameEvent $triggeredEvent */
                foreach ($triggeredEvents as $triggeredEvent) {
                    $this->callEvent($triggeredEvent, $triggeredEvent->getEventName());
                }
            }

            $this->dispatchAppliedModifiers($modifier, $event->getTags(), $event->getTime());
        }
    }

    private function applyReplaceModifiers(
        ModifierCollection $triggerModifiers,
        AbstractGameEvent $event,
        bool $dispatch
    ): ?AbstractGameEvent {
        $time = $event->getTime();
        $tags = $event->getTags();

        foreach ($triggerModifiers as $modifier) {
            $tags[] = $modifier->getModifierConfig()->getModifierName() ?: $modifier->getModifierConfig()->getName();

            /** @var TriggerEventModifierConfig $modifierConfig */
            $modifierConfig = $modifier->getModifierConfig();

            $triggeredEventConfig = $modifierConfig->getTriggeredEvent();

            if ($triggeredEventConfig === null) {
                if ($dispatch === true) {
                    $this->dispatchAppliedModifiers($modifier, $event->getTags(), $event->getTime());
                }

                return null;
            }

            if (!$event->isModified()) {
                // @TODO better handle event creation
                $event = $this->eventCreationService->createEvents(
                    $triggeredEventConfig,
                    $modifier->getModifierHolder(),
                    $event->getAuthor(),
                    $tags,
                    $event->getTime()
                )[0];
            }

            if ($dispatch === true) {
                $this->dispatchAppliedModifiers($modifier, $tags, $time);
            }
        }

        return $event;
    }

    private function applyVariableModifiers(ModifierCollection $variableModifiers, AbstractGameEvent $event, bool $dispatch = true): AbstractGameEvent
    {
        if (!($event instanceof VariableEventInterface)) {
            return $event;
        }

        if (!$event->isModified()) {
            $event = $this->modifierService->applyVariableModifiers($variableModifiers, $event);
        }

        if ($dispatch) {
            foreach ($variableModifiers as $modifier) {
                $this->dispatchAppliedModifiers($modifier, $event->getTags(), $event->getTime());
            }
        }

        return $event;
    }

    private function dispatchAppliedModifiers(GameModifier $modifier, array $tags, \DateTime $time)
    {
        $modifierEvent = new ModifierEvent($modifier, $tags, $time, true);
        $modifierEvent->setEventName(ModifierEvent::APPLY_MODIFIER);
        $this->callEvent($modifierEvent, ModifierEvent::APPLY_MODIFIER);
    }

    /**
     * @throws \Exception
     */
    public function computeEventModifications(AbstractGameEvent $event, string $name): ?AbstractGameEvent
    {
        $event->setEventName($name);

        $event = $this->applyModifiers($event, false);

        if ($event !== null) {
            $event->setIsModified(true);
        }

        return $event;
    }

    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string
    {
        $event->setEventName($name);

        $modifiers = $event->getModifiers();
        $modifiers = $this->modifierRequirementService->getActiveModifiers($modifiers, $event->getTags());
        $preventModifiers = $modifiers->getTriggerEventModifiersReplace()->filter(
            fn (GameModifier $modifier) => (
                ($modifierConfig = $modifier->getModifierConfig()) instanceof TriggerEventModifierConfig
                && $modifierConfig->getTriggeredEvent() === null
            )
        );

        if ($preventModifiers->count() > 0) {
            /** @var GameModifier $preventModifier */
            $preventModifier = $preventModifiers->first();

            return $preventModifier->getModifierConfig()->getModifierName() ?: $preventModifier->getModifierConfig()->getName();
        }

        return null;
    }
}
