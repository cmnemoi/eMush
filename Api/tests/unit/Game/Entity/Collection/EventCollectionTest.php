<?php

namespace Mush\Tests\unit\Game\Entity\Collection;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EventCollectionTest extends TestCase
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

        self::assertCount(5, $mergedCollection);
        self::assertSame($event1, $mergedCollection->first());
        self::assertSame($event3, $mergedCollection->next());
        self::assertSame($event2, $mergedCollection->next());
        self::assertSame($event4, $mergedCollection->next());
        self::assertSame($event5, $mergedCollection->next());
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
        self::assertCount(2, $eventCollection);
        self::assertSame($event3, $eventCollection->first());
        self::assertSame($event4, $eventCollection->next());

        $eventCollection = $eventCollection->addEvent($event2);
        self::assertCount(3, $eventCollection);
        self::assertSame($event2, $eventCollection->first());
        self::assertSame($event3, $eventCollection->next());
        self::assertSame($event4, $eventCollection->next());

        $eventCollection = $eventCollection->addEvent($event5);
        self::assertCount(4, $eventCollection);
        self::assertSame($event2, $eventCollection->first());
        self::assertSame($event3, $eventCollection->next());
        self::assertSame($event4, $eventCollection->next());
        self::assertSame($event5, $eventCollection->next());

        $eventCollection = $eventCollection->addEvent($event1);
        self::assertCount(5, $eventCollection);
        self::assertSame($event1, $eventCollection->first());
        self::assertSame($event2, $eventCollection->next());
        self::assertSame($event3, $eventCollection->next());
        self::assertSame($event4, $eventCollection->next());
        self::assertSame($event5, $eventCollection->next());
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

        self::assertSame($event4, $initialEvent);
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

        self::assertCount(4, $updatedEvents);

        self::assertSame($event1, $updatedEvents->first());
        self::assertSame($event3, $updatedEvents->next());

        $eventTest = $updatedEvents->next();
        self::assertSame($event2, $eventTest);
        self::assertInstanceOf(AbstractGameEvent::class, $eventTest);
        self::assertSame(0, $eventTest->getPriority());

        self::assertSame($event5, $updatedEvents->next());
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
        self::assertEmpty($updatedEvents);
        self::assertNotContains($event1, $updatedEvents);
        self::assertNotContains($event2, $updatedEvents);
        self::assertNotContains($event3, $updatedEvents);
        self::assertNotContains($event4, $updatedEvents);
        self::assertNotContains($event5, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(0);
        self::assertCount(4, $updatedEvents);
        self::assertContains($event1, $updatedEvents);
        self::assertContains($event2, $updatedEvents);
        self::assertContains($event3, $updatedEvents);
        self::assertContains($event4, $updatedEvents);
        self::assertNotContains($event5, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(-1);
        self::assertCount(3, $updatedEvents);
        self::assertContains($event1, $updatedEvents);
        self::assertContains($event2, $updatedEvents);
        self::assertContains($event3, $updatedEvents);
        self::assertNotContains($event4, $updatedEvents);
        self::assertNotContains($event5, $updatedEvents);

        $updatedEvents = $eventCollection->stopEvents(10);
        self::assertCount(5, $updatedEvents);
        self::assertContains($event1, $updatedEvents);
        self::assertContains($event2, $updatedEvents);
        self::assertContains($event3, $updatedEvents);
        self::assertContains($event4, $updatedEvents);
        self::assertContains($event5, $updatedEvents);
    }
}
