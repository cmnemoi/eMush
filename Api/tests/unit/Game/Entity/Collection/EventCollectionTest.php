<?php

namespace Mush\Test\Event\Entity\Collection;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use PHPUnit\Framework\TestCase;

class EventCollectionTest extends TestCase
{
    public function testMergeEventChains()
    {
        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2);

        $event2 = new AbstractGameEvent([], new \DateTime());
        $event2->setPriority(-1);

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1);

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0);

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1);

        $eventCollection1 = new EventChain([$event4, $event2]);
        $eventCollection2 = new EventChain([$event1, $event3, $event5]);

        $mergedCollection = $eventCollection1->addEvents($eventCollection2);
        $this->assertCount(5, $mergedCollection);
    }

    public function testSortEvent()
    {
        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2);

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1);

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0);

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1);

        $eventCollection = new EventChain([$event4, $event1, $event3, $event5]);

        $sortedCollection = $eventCollection->sortEvents();

        $this->assertCount(4, $sortedCollection);
        $this->assertEquals($event1, $sortedCollection->first());
        $this->assertEquals($event3, $sortedCollection->next());
        $this->assertEquals($event4, $sortedCollection->next());
        $this->assertEquals($event5, $sortedCollection->next());
    }

    public function testGetInitialEvent()
    {
        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2);

        $event2 = new AbstractGameEvent([], new \DateTime());
        $event2->setPriority(-1);

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1);

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0);

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1);

        $eventCollection = new EventChain([$event4, $event2, $event1, $event3, $event5]);

        $initialEvent = $eventCollection->getInitialEvent();

        $this->assertEquals($event4, $initialEvent);
    }

    public function testUpdateInitialEvent()
    {
        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2)->setEventName('one');

        $event2 = new AbstractGameEvent([], new \DateTime());
        $event2->setPriority(-1)->setEventName('two');

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1)->setEventName('three');

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0)->setEventName('four');

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1)->setEventName('five');

        $eventCollection = new EventChain([$event4, $event1, $event3, $event5]);

        $updatedEvents = $eventCollection->updateInitialEvent($event2);

        $this->assertCount(4, $updatedEvents);

        $this->assertContains($event1, $updatedEvents);
        $this->assertContains($event3, $updatedEvents);
        $this->assertContains($event5, $updatedEvents);
        $eventTest = $updatedEvents->last();
        $this->assertInstanceOf(AbstractGameEvent::class, $eventTest);
        $this->assertEquals('two', $eventTest->getEventName());
        $this->assertEquals(0, $eventTest->getPriority());
    }

    public function testStopEvent()
    {
        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2)->setEventName('one');

        $event2 = new AbstractGameEvent([], new \DateTime());
        $event2->setPriority(-1)->setEventName('two');

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1)->setEventName('three');

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0)->setEventName('four');

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1)->setEventName('five');

        $eventCollection = new EventChain([$event4, $event1, $event3, $event5]);

        $updatedEvents = $eventCollection->stopEvents(-10);
        $this->assertEmpty($updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(0);
        $this->assertCount(2, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(-1);
        $this->assertCount(1, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(10);
        $this->assertCount(4, $updatedEvents);
    }
}
