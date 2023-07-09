<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Event\AbstractGameEvent;

/**
 * @template-extends ArrayCollection<int, AbstractGameEvent>
 */
class EventCollection extends ArrayCollection
{
    public function addEvent(AbstractGameEvent $event): self
    {
        $priority = $event->getPriority();

        // find where to add the new event
        $lowerPriority = $this->filter(fn (AbstractGameEvent $event) => $event->getPriority()<$priority);
        $eventArray = $this->toArray();

        if($lowerPriority->count() === $this->count()) {
            $this->add($event);

            return $this;
        }

        $eventArray = array_splice($eventArray, 0, 1, [$event]);

        return new EventCollection($eventArray);
    }
}
