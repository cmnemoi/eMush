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

        $eventCollection1 = new EventChain([$event2, $event4]);
        $eventCollection2 = new EventChain([$event1, $event3, $event5]);

        $mergedCollection = $eventCollection1->addEvents($eventCollection2);

        $this->assertCount(5, $mergedCollection);
        $this->assertEquals($event1, $mergedCollection->first());
        $this->assertEquals($event3, $mergedCollection->next());
        $this->assertEquals($event2, $mergedCollection->next());
        $this->assertEquals($event4, $mergedCollection->next());
        $this->assertEquals($event5, $mergedCollection->next());
    }

    public function testAddEvent()
    {
        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2)->setEventName('event1');

        $event2 = new AbstractGameEvent([], new \DateTime());
        $event2->setPriority(-1)->setEventName('event2');

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1)->setEventName('event3');

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0)->setEventName('event4');

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1)->setEventName('event5');

        $eventCollection = new EventChain([$event4]);

        $eventCollection = $eventCollection->addEvent($event3);
        $this->assertCount(2, $eventCollection);
        $this->assertEquals($event3, $eventCollection->first());
        $this->assertEquals($event4, $eventCollection->next());

        $eventCollection = $eventCollection->addEvent($event2);
        $this->assertCount(3, $eventCollection);
        $this->assertEquals($event2, $eventCollection->first());
        $this->assertEquals($event3, $eventCollection->next());
        $this->assertEquals($event4, $eventCollection->next());

        $eventCollection = $eventCollection->addEvent($event5);
        $this->assertCount(4, $eventCollection);
        $this->assertEquals($event2, $eventCollection->first());
        $this->assertEquals($event3, $eventCollection->next());
        $this->assertEquals($event4, $eventCollection->next());
        $this->assertEquals($event5, $eventCollection->next());

        $eventCollection = $eventCollection->addEvent($event1);
        $this->assertCount(5, $eventCollection);
        $this->assertEquals($event1, $eventCollection->first());
        $this->assertEquals($event2, $eventCollection->next());
        $this->assertEquals($event3, $eventCollection->next());
        $this->assertEquals($event4, $eventCollection->next());
        $this->assertEquals($event5, $eventCollection->next());
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

        $eventCollection = new EventChain([$event1, $event3, $event4, $event5]);

        $updatedEvents = $eventCollection->updateInitialEvent($event2);

        $this->assertCount(4, $updatedEvents);

        $this->assertEquals($event1, $updatedEvents->first());
        $this->assertEquals($event3, $updatedEvents->next());

        $eventTest = $updatedEvents->next();
        $this->assertEquals($event2, $eventTest);
        $this->assertInstanceOf(AbstractGameEvent::class, $eventTest);
        $this->assertEquals(0, $eventTest->getPriority());

        $this->assertEquals($event5, $updatedEvents->next());
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

        $eventCollection = new EventChain([
            $event1, $event2, $event3, $event4, $event5,
        ]);

        $updatedEvents = $eventCollection->stopEvents(-10);
        $this->assertEmpty($updatedEvents);
        $this->assertNotContains($event1, $updatedEvents);
        $this->assertNotContains($event2, $updatedEvents);
        $this->assertNotContains($event3, $updatedEvents);
        $this->assertNotContains($event4, $updatedEvents);
        $this->assertNotContains($event5, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(0);
        $this->assertCount(4, $updatedEvents);
        $this->assertContains($event1, $updatedEvents);
        $this->assertContains($event2, $updatedEvents);
        $this->assertContains($event3, $updatedEvents);
        $this->assertContains($event4, $updatedEvents);
        $this->assertNotContains($event5, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(-1);
        $this->assertCount(3, $updatedEvents);
        $this->assertContains($event1, $updatedEvents);
        $this->assertContains($event2, $updatedEvents);
        $this->assertContains($event3, $updatedEvents);
        $this->assertNotContains($event4, $updatedEvents);
        $this->assertNotContains($event5, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(10);
        $this->assertCount(5, $updatedEvents);
        $this->assertContains($event1, $updatedEvents);
        $this->assertContains($event2, $updatedEvents);
        $this->assertContains($event3, $updatedEvents);
        $this->assertContains($event4, $updatedEvents);
        $this->assertContains($event5, $updatedEvents);
    }
}
