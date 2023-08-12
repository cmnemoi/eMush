<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
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
            $this->eventDispatcher->dispatch($event, $event->getEventName());
        }

        return $events;
    }

    /**
     * @throws \Exception
     */
    private function applyModifiers(AbstractGameEvent $event): EventChain
    {
        $modifiers = $event->getModifiers();

        return $this->modifierService->applyModifiers($modifiers, $event);
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

    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string
    {
        $event->setEventName($name);

        $events = $this->applyModifiers($event);

        $lastEvent = $events->last();

        if ($lastEvent instanceof ModifierEvent) {
            $modifierConfig = $lastEvent->getModifier()->getModifierConfig();

            return $modifierConfig->getModifierName() ?: $modifierConfig->getName();
        }

        return null;
    }
}
