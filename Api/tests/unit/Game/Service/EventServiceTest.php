<?php

namespace Mush\Tests\unit\Game\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class EventServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcherService;

    /** @var EventModifierServiceInterface|Mockery\Mock */
    private EventModifierServiceInterface $eventModifierService;

    /** @var Mockery\Mock|ModifierRequirementServiceInterface */
    private EventServiceInterface $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->eventDispatcherService = \Mockery::mock(EventDispatcherInterface::class);
        $this->eventModifierService = \Mockery::mock(EventModifierServiceInterface::class);

        $this->eventModifierService->shouldReceive('persist');
        $this->eventModifierService->shouldReceive('flush');

        $this->service = new EventService(
            $this->eventDispatcherService,
            $this->eventModifierService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testCallNoModifier()
    {
        $event = new AbstractGameEvent(['test'], new \DateTime());

        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent->getTags() === $event->getTags()
                && $dispatchedEvent->getEventName() === 'eventName'
                && $eventName === 'eventName'
                && $dispatchedEvent->getTime() === $event->getTime()
            ))
            ->once();

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();

        $this->service->callEvent($event, 'eventName');
    }

    public function testPreviewNoModifier()
    {
        $time = new \DateTime();
        $event = new AbstractGameEvent(['test'], $time);

        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        self::assertSame('eventName', $newEvent->getEventName());
        self::assertSame(['test'], $newEvent->getTags());
        self::assertSame($time, $newEvent->getTime());
    }

    public function testCallTriggerModifier()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new AbstractGameEvent(['test'], $time);
        $event->setAuthor($player);

        $eventConfig = new VariableEventConfig();
        $eventConfig->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $modifierConfig = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setTriggeredEvent($eventConfig);

        $modifier = new GameModifier($player, $modifierConfig);
        $triggeredEvent = new PlayerCycleEvent(
            $player,
            ['triggeredTag', 'test'],
            $time
        );
        $triggeredEvent->setEventName(StatusEvent::STATUS_APPLIED)->setPriority(-1);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$triggeredEvent, $modifierEvent, $event]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();

        // dispatch the event triggered by the modifier
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($triggeredEvent, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$triggeredEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($triggeredEvent, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$triggeredEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($triggeredEvent, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$triggeredEvent]))
            ->once();

        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $triggeredEvent
                && $eventName === StatusEvent::STATUS_APPLIED
            ))
            ->once();
        // dispatch the modifier Application
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifierEvent
                && $eventName === ModifierEvent::APPLY_MODIFIER
            ))
            ->once();
        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $event
                && $eventName === 'eventName'
            ))
            ->once();
        $this->service->callEvent($event, 'eventName');
    }

    public function testPreviewTriggerModifier()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new AbstractGameEvent(['test'], $time);
        $event->setAuthor($player);

        $eventConfig = new VariableEventConfig();
        $eventConfig->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $triggeredEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            1,
            ['triggeredTag'],
            $time
        );
        $triggeredEvent->setEventName(VariableEventInterface::CHANGE_VARIABLE)->setPriority(-1);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$triggeredEvent, $event]))
            ->once();
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        self::assertSame($event, $newEvent);
    }

    public function testCallVariableModifier()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new DaedalusVariableEvent($daedalus, DaedalusVariableEnum::FUEL, 2, ['test'], $time);
        $event->setAuthor($player);

        $modifiedEvent = new DaedalusVariableEvent($daedalus, DaedalusVariableEnum::FUEL, 4, ['test'], $time);
        $modifiedEvent->setAuthor($player)->setEventName('eventName')->setPriority(0);

        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setTargetVariable(DaedalusVariableEnum::FUEL)
            ->setDelta(3)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE);
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent, $modifiedEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifiedEvent, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$modifiedEvent]))
            ->once();

        // dispatch the modifier Application
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifierEvent
                && $eventName === ModifierEvent::APPLY_MODIFIER
            ))
            ->once();

        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifiedEvent
                && $eventName === 'eventName'
            ))
            ->once();
        $this->service->callEvent($event, 'eventName');
    }

    public function testPreviewVariableModifier()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new DaedalusVariableEvent($daedalus, DaedalusVariableEnum::FUEL, 2, ['test'], $time);
        $event->setAuthor($player);

        $modifiedEvent = new DaedalusVariableEvent($daedalus, DaedalusVariableEnum::FUEL, 4, ['test'], $time);
        $modifiedEvent->setAuthor($player)->setEventName('eventName');

        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setTargetVariable(DaedalusVariableEnum::FUEL)
            ->setDelta(3)
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE);
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent, $modifiedEvent]))
            ->once();

        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        self::assertInstanceOf(DaedalusVariableEvent::class, $newEvent);
        self::assertSame($modifiedEvent, $newEvent);
    }

    public function testPreventEvent()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new AbstractGameEvent(['test'], $time);
        $event->setAuthor($player);

        $modifierConfig = new EventModifierConfig('unitTestPreventEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName');
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$event]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();

        // dispatch the modifier Application
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::PRE_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent, ModifierPriorityEnum::POST_MODIFICATION)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(static fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifierEvent
                && $eventName === ModifierEvent::APPLY_MODIFIER
            ))
            ->once();

        $this->service->callEvent($event, 'eventName');
    }

    public function testReturnEventPreventedReason()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new AbstractGameEvent(['test'], $time);
        $event->setAuthor($player);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, [ModifierPriorityEnum::PREVENT_EVENT])
            ->andReturn(new EventChain([$event]))
            ->once();

        $reason = $this->service->eventCancelReason($event, 'eventName');
        self::assertNull($reason);

        $modifierConfig = new EventModifierConfig('unitTestPreventEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT);
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventDispatcherService->shouldReceive('dispatch')->never();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event, [ModifierPriorityEnum::PREVENT_EVENT])
            ->andReturn(new EventChain([$modifierEvent]))
            ->once();
        $reason = $this->service->eventCancelReason($event, 'eventName');

        self::assertSame('modifierName', $reason);
    }
}
