<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Event\AbstractGameEvent;

/**
 * @template-extends ArrayCollection<int, AbstractGameEvent>
 */
class EventChain extends ArrayCollection
{
    /**
     * `EventChain::getInitialEvent` will return the event that initiated the chain.
     */
    public function getInitialEvent(): ?AbstractGameEvent
    {
        $initialEvents = $this->filter(fn (AbstractGameEvent $event) => $event->getPriority() === 0);

        $initialEvent = $initialEvents->first();
        // Maybe the initial event has already been removed (prevent modifier)
        if ($initialEvent === false) {
            return null;
        }

        return $initialEvent;
    }

    /**
     * `EventChain::addEvent() will add an event at a position given by the priority.
     */
    public function addEvent(AbstractGameEvent $newEvent): self
    {
        // find where to add the new event
        $lowerPriority = $this->filter(fn (AbstractGameEvent $event) => $event->getPriority() < $newEvent->getPriority())->count();

        $eventArray = $this->toArray();
        array_splice($eventArray, $lowerPriority, 0, [$newEvent]);

        return new EventChain($eventArray);
    }

    /**
     * `EventChain::addEvents()` will merge two event chain.
     */
    public function addEvents(EventChain $eventsToAdd): self
    {
        $newEventChain = $this;
        foreach ($eventsToAdd as $event) {
            $newEventChain = $newEventChain->addEvent($event);
        }

        return $newEventChain;
    }

    /**
     * `EventChain::updateInitialEvent()` will replace the initial event with an updated version.
     */
    public function updateInitialEvent(AbstractGameEvent $newEvent): self
    {
        // find the initial event, i.e. the event with a priority of 0
        $initialEvents = $this->getInitialEvent();
        // Maybe the initial event has already been removed (prevent modifier)
        if ($initialEvents === null) {
            return $this;
        }

        $lowerPriority = $this->filter(fn (AbstractGameEvent $event) => $event->getPriority() === 0)->count();
        $eventArray = $this->toArray();
        $newEvent->setPriority(0);

        array_splice($eventArray, $lowerPriority + 1, 1, [$newEvent]);

        return new EventChain($eventArray);
    }

    /**
     * `EventChain::stopEvents()` will stop the event chain at a given priority.
     */
    public function stopEvents(int $priority): self
    {
        // find where to add the new event
        $lowerPriority = $this->filter(fn (AbstractGameEvent $event) => $event->getPriority() <= $priority)->count();

        $eventArray = $this->toArray();
        array_splice($eventArray, $lowerPriority);

        return new EventChain($eventArray);
    }
}
