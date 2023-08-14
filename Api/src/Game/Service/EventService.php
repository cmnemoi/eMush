<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
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
            $event->setTags(array_merge(
                $event->getTags(),
                array_merge($caller->getTags())
            ));
        }
        $event->setEventName($name);

        $events = $this->applyModifiers($event);

        foreach ($events as $event) {
            if ($event->getPriority() !== 0) {
                // a condition in triggerEventModifierConfig allow to avoid infinite loop
                // This allows triggered events to be themselves modified
                $this->callEvent($event, $event->getEventName());
            } else {
                $this->eventDispatcher->dispatch($event, $event->getEventName());
            }
        }

        return $events;
    }

    /**
     * @throws \Exception
     */
    private function applyModifiers(AbstractGameEvent $event): EventChain
    {
        $event->setPriority(0);

        return $this->modifierService->applyModifiers($event);
    }

    /**
     * @throws \Exception
     */
    public function computeEventModifications(AbstractGameEvent $event, string $name): ?AbstractGameEvent
    {
        $event->setEventName($name);

        $events = $this->applyModifiers($event);

        return $events->getInitialEvent();
    }

    /**
     * @throws \Exception
     */
    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string
    {
        $event->setEventName($name);

        $events = $this->applyModifiers($event);

        if ($events->getInitialEvent() !== null) {
            return null;
        }

        /** @var AbstractGameEvent $lastEvent */
        $lastEvent = $events->last();

        /** @var ModifierEvent $preventEvent */
        $preventEvent = $events->filter(fn (AbstractGameEvent $event) => (
            $event->getPriority() === $lastEvent->getPriority() &&
            $event instanceof ModifierEvent &&
            $event->getModifier()->getModifierConfig()->getModifierStrategy() === ModifierStrategyEnum::PREVENT_EVENT
        ))->first();

        $modifierConfig = $preventEvent->getModifier()->getModifierConfig();

        return $modifierConfig->getModifierName() ?: $modifierConfig->getName();
    }
}
