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
     * `EventChain::sortEvent` will sort the events according to their priority.
     */
    public function sortEvents(): self
    {
        $array = $this->toArray();

        usort($array, function ($a, $b) {
            return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
        });

        return new EventChain($array);
    }

    /**
     * `EventChain::addEvent()` will merge two event chain.
     */
    public function addEvents(EventChain $eventsToAdd): self
    {
        return new EventChain(array_merge($this->toArray(), $eventsToAdd->toArray()));
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

        $newEvent->setPriority(0);

        $this->removeElement($initialEvents);
        $this->add($newEvent);

        return $this;
    }

    /**
     * `EventChain::stopEvents()` will stop the event chain at a given priority.
     */
    public function stopEvents(int $priority): self
    {
        $sortedEvents = $this->sortEvents();
        // find where to add the new event
        $lowerPriority = $sortedEvents->filter(fn (AbstractGameEvent $event) => $event->getPriority() < $priority)->count();

        $eventArray = $sortedEvents->toArray();
        array_splice($eventArray, $lowerPriority);

        return new EventChain($eventArray);
    }
}
