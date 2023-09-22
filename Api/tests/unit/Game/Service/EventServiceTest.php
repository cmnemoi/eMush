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

class EventServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcherService;
    /** @var EventModifierServiceInterface|Mockery\Mock */
    private EventModifierServiceInterface $eventModifierService;
    /** @var ModifierRequirementServiceInterface|Mockery\Mock */
    private EventServiceInterface $service;

    /**
     * @before
     */
    public function before()
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
    public function after()
    {
        \Mockery::close();
    }

    public function testCallNoModifier()
    {
        $event = new AbstractGameEvent(['test'], new \DateTime());

        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent->getTags() === $event->getTags()
                && $dispatchedEvent->getEventName() === 'eventName'
                && $eventName === 'eventName'
                && $dispatchedEvent->getTime() === $event->getTime()
            ))
            ->once()
        ;

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$event]))
            ->once()
        ;

        $this->service->callEvent($event, 'eventName');
    }

    public function testPreviewNoModifier()
    {
        $time = new \DateTime();
        $event = new AbstractGameEvent(['test'], $time);

        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$event]))
            ->once()
        ;

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        $this->assertEquals('eventName', $newEvent->getEventName());
        $this->assertEquals(['test'], $newEvent->getTags());
        $this->assertEquals($time, $newEvent->getTime());
    }

    public function testCallTriggerModifierDoNotApplyOnEvent()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);
        $event = new AbstractGameEvent(['test'], new \DateTime());
        $event->setAuthor($player);

        $eventConfig = new VariableEventConfig();
        $eventConfig->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $modifierConfig = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig
            ->setModifierName('modifier')
            ->setTargetEvent('otherEvent')
            ->setTriggeredEvent($eventConfig)
        ;

        $modifier = new GameModifier($player, $modifierConfig);

        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent->getTags() === $event->getTags()
                && $dispatchedEvent->getEventName() === 'eventName'
                && $eventName === 'eventName'
                && $dispatchedEvent->getTime() === $event->getTime()
            ))
            ->once()
        ;

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$event]))
            ->once()
        ;

        $this->service->callEvent($event, 'eventName');
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
            ->setTriggeredEvent($eventConfig)
        ;

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
            ->with($event)
            ->andReturn(new EventChain([$triggeredEvent, $modifierEvent, $event]))
            ->once()
        ;
        // dispatch the triggered event
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($triggeredEvent)
            ->andReturn(new EventChain([$triggeredEvent]))
            ->once()
        ;
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $triggeredEvent
                && $eventName === StatusEvent::STATUS_APPLIED
            ))
            ->once()
        ;
        // dispatch the modifier Application
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once()
        ;
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifierEvent
                && $eventName === ModifierEvent::APPLY_MODIFIER
            ))
            ->once()
        ;
        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $event
                && $eventName === 'eventName'
            ))
            ->once()
        ;
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

        $modifierConfig = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setTriggeredEvent($eventConfig)
        ;

        $modifier = new GameModifier($player, $modifierConfig);

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
            ->with($event)
            ->andReturn(new EventChain([$triggeredEvent, $event]))
            ->once()
        ;
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        $this->assertEquals($event, $newEvent);
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
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$modifierEvent, $modifiedEvent]))
            ->once()
        ;

        // dispatch the modifier Application
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once()
        ;
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifierEvent
                && $eventName === ModifierEvent::APPLY_MODIFIER
            ))
            ->once()
        ;

        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifiedEvent
                && $eventName === 'eventName'
            ))
            ->once()
        ;
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
            ->setMode(VariableModifierModeEnum::MULTIPLICATIVE)
        ;
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$modifierEvent, $modifiedEvent]))
            ->once()
        ;

        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        $this->assertInstanceOf(DaedalusVariableEvent::class, $newEvent);
        $this->assertEquals($modifiedEvent, $newEvent);
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
            ->setTargetEvent('eventName')
        ;
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once()
        ;

        // dispatch the modifier Application
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($modifierEvent)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once()
        ;
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent === $modifierEvent
                && $eventName === ModifierEvent::APPLY_MODIFIER
            ))
            ->once()
        ;

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
            ->with($event)
            ->andReturn(new EventChain([$event]))
            ->once()
        ;

        $reason = $this->service->eventCancelReason($event, 'eventName');
        $this->assertNull($reason);

        $modifierConfig = new EventModifierConfig('unitTestPreventEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setModifierStrategy(ModifierStrategyEnum::PREVENT_EVENT)
        ;
        $modifier = new GameModifier($player, $modifierConfig);

        $modifierEvent = new ModifierEvent($modifier, ['test', 'modifierName'], $time);
        $modifierEvent->setPriority(-1)->setEventName(ModifierEvent::APPLY_MODIFIER);

        $this->eventDispatcherService->shouldReceive('dispatch')->never();
        $this->eventModifierService
            ->shouldReceive('applyModifiers')
            ->with($event)
            ->andReturn(new EventChain([$modifierEvent]))
            ->once()
        ;
        $reason = $this->service->eventCancelReason($event, 'eventName');

        $this->assertEquals('modifierName', $reason);
    }
}
