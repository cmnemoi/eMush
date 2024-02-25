<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventService implements EventServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private EventModifierServiceInterface $modifierService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventModifierServiceInterface $modifierService,
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->modifierService = $modifierService;
    }

    /**
     * @throws \Exception
     */
    public function callEvent(AbstractGameEvent $event, string $name, AbstractGameEvent $caller = null): EventChain
    {
        if ($caller !== null) {
            $event->addTags($caller->getTags());
        }
        $event->setEventName($name);

        // first dispatch events created by modifiers with before_event priority
        $preEvents = $this->applyModifiers($event, ModifierPriorityEnum::PRE_MODIFICATION);

        $initialEvent = $this->dispatchEventChain($preEvents, false);
        if ($initialEvent === null) {
            return $preEvents;
        }

        // then dispatch the initial event and its modifications
        $simultaneousEvents = $this->applyModifiers($initialEvent, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);
        $initialEvent->setIsModified(true);
        $modifiedInitialEvent = $this->dispatchEventChain($simultaneousEvents, true);

        if ($modifiedInitialEvent === null) {
            return $simultaneousEvents;
        }

        // finally dispatch events created by modifiers with after_event priority
        $postEvents = $this->applyModifiers($modifiedInitialEvent, ModifierPriorityEnum::POST_MODIFICATION);
        $this->dispatchEventChain($postEvents, false);

        return $postEvents;
    }

    private function dispatchEventChain(EventChain $eventChain, bool $dispatchInitialEvent): ?AbstractGameEvent
    {
        foreach ($eventChain as $modifierEvent) {
            // the initial event have already been dispatched
            if ($modifierEvent->getPriority() !== 0) {
                // store event priority as it is going to be set to 0 when computing modifiers
                $priority = $modifierEvent->getPriority();

                $this->callEvent($modifierEvent, $modifierEvent->getEventName());

                // reset the priority to its previous value
                $modifierEvent->setPriority($priority);
            } elseif ($dispatchInitialEvent) {
                $this->eventDispatcher->dispatch($modifierEvent, $modifierEvent->getEventName());
            }
        }

        return $eventChain->getInitialEvent();
    }

    /**
     * @throws \Exception
     */
    private function applyModifiers(AbstractGameEvent $event, array $priorities): EventChain
    {
        $event->setPriority(0);

        return $this->modifierService->applyModifiers($event, $priorities);
    }

    /**
     * @throws \Exception
     */
    public function computeEventModifications(AbstractGameEvent $event, string $name): ?AbstractGameEvent
    {
        $event->setEventName($name);

        $events = $this->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);
        $initialEvent = $events->getInitialEvent();

        if ($initialEvent !== null) {
            $initialEvent->setIsModified(true);
        }

        return $initialEvent;
    }

    /**
     * @throws \Exception
     */
    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string
    {
        $event->setEventName($name);

        $events = $this->applyModifiers($event, [ModifierPriorityEnum::PREVENT_EVENT]);

        if ($events->getInitialEvent() !== null) {
            return null;
        }

        /** @var AbstractGameEvent $lastEvent */
        $lastEvent = $events->last();

        /** @var ModifierEvent $preventEvent */
        $preventEvent = $events->filter(fn (AbstractGameEvent $event) => (
            $event->getPriority() === $lastEvent->getPriority()
            && $event instanceof ModifierEvent
            && $event->getModifier()->getModifierConfig()->getModifierStrategy() === ModifierStrategyEnum::PREVENT_EVENT
        ))->first();

        $modifierConfig = $preventEvent->getModifier()->getModifierConfig();

        return $modifierConfig->getModifierName() ?: $modifierConfig->getName();
    }
}
