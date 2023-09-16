<?php

namespace Mush\Tests\unit\Game\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Modifier\Service\EventCreationServiceInterface;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerVariableEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcherService;
    /** @var EventModifierServiceInterface|Mockery\Mock */
    private EventModifierServiceInterface $eventModifierService;
    /** @var ModifierRequirementServiceInterface|Mockery\Mock */
    private ModifierRequirementServiceInterface $modifierRequirementService;
    /** @var EventCreationServiceInterface|Mockery\Mock */
    private EventCreationServiceInterface $eventCreationService;

    private EventServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcherService = \Mockery::mock(EventDispatcherInterface::class);
        $this->eventModifierService = \Mockery::mock(EventModifierServiceInterface::class);
        $this->modifierRequirementService = \Mockery::mock(ModifierRequirementServiceInterface::class);
        $this->eventCreationService = \Mockery::mock(EventCreationServiceInterface::class);

        $this->eventModifierService->shouldReceive('persist');
        $this->eventModifierService->shouldReceive('flush');

        $this->service = new EventService(
            $this->eventDispatcherService,
            $this->eventModifierService,
            $this->modifierRequirementService,
            $this->eventCreationService
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
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;
        $this->service->callEvent($event, 'eventName');
    }

    public function testPreviewNoModifier()
    {
        $time = new \DateTime();
        $event = new AbstractGameEvent(['test'], $time);

        $this->eventDispatcherService->shouldReceive('dispatch')->never();
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
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
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
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
            ['triggeredTag'],
            $time
        );
        $triggeredEvent->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventCreationService->shouldReceive('createEvents')
            ->with($eventConfig, $player, $player, ['test', 'modifierName'], $time)
            ->andReturn([$triggeredEvent])
            ->once()
        ;
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->twice()
        ;
        // dispatch the triggered event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof PlayerCycleEvent
                && $dispatchedEvent->getTags() === $triggeredEvent->getTags()
                && $dispatchedEvent->getEventName() === VariableEventInterface::CHANGE_VARIABLE
                && $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $dispatchedEvent->getTime() === $time
            ))
            ->once()
        ;
        // dispatch the modifier Application
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof ModifierEvent
                && $dispatchedEvent->getTags() === ['test', 'modifierName']
                && $dispatchedEvent->getEventName() === ModifierEvent::APPLY_MODIFIER
                && $eventName === ModifierEvent::APPLY_MODIFIER
                && $dispatchedEvent->getTime() === $time
            ))
            ->once()
        ;
        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent->getTags() === $event->getTags()
                && $dispatchedEvent->getEventName() === 'eventName'
                && $eventName === 'eventName'
                && $dispatchedEvent->getTime() === $event->getTime()
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
        $triggeredEvent->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventCreationService->shouldReceive('createEvents')->never();
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        $this->assertEquals('eventName', $newEvent->getEventName());
        $this->assertEquals(['test'], $newEvent->getTags());
        $this->assertEquals($time, $newEvent->getTime());
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

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventModifierService->shouldReceive('applyVariableModifiers')
            ->withArgs(fn (ModifierCollection $modifiers, AbstractGameEvent $functionEvent) => (
                $modifiers->count() === 1 && $modifiers->first() === $modifier
                && $functionEvent instanceof DaedalusVariableEvent
                && $functionEvent->getEventName() === 'eventName'
                && $functionEvent->getTime() === $time
                && $functionEvent->getQuantity() === $event->getQuantity()
            ))
            ->andReturn($modifiedEvent)
            ->once()
        ;

        // dispatch the modifier Application
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof ModifierEvent
                && $dispatchedEvent->getTags() === ['test', 'modifierName']
                && $dispatchedEvent->getEventName() === ModifierEvent::APPLY_MODIFIER
                && $eventName === ModifierEvent::APPLY_MODIFIER
                && $dispatchedEvent->getTime() === $time
            ))
            ->once()
        ;
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;
        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof DaedalusVariableEvent
                && $dispatchedEvent->getTags() === $modifiedEvent->getTags()
                && $dispatchedEvent->getEventName() === 'eventName'
                && $eventName === 'eventName'
                && $dispatchedEvent->getTime() === $modifiedEvent->getTime()
                && $dispatchedEvent->getQuantity() === $modifiedEvent->getQuantity()
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

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventModifierService->shouldReceive('applyVariableModifiers')
            ->withArgs(fn (ModifierCollection $modifiers, AbstractGameEvent $functionEvent) => (
                $modifiers->count() === 1 && $modifiers->first() === $modifier
                && $functionEvent instanceof DaedalusVariableEvent
                && $functionEvent->getEventName() === 'eventName'
                && $functionEvent->getTime() === $time
                && $functionEvent->getQuantity() === $event->getQuantity()
            ))
            ->andReturn($modifiedEvent)
            ->once()
        ;

        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        $this->assertTrue($newEvent->isModified());
        $this->assertInstanceOf(DaedalusVariableEvent::class, $newEvent);
        $this->assertEquals('eventName', $newEvent->getEventName());
        $this->assertEquals(['test'], $newEvent->getTags());
        $this->assertEquals($time, $newEvent->getTime());
        $this->assertEquals(4, $newEvent->getQuantity());
        $this->assertEquals(DaedalusVariableEnum::FUEL, $newEvent->getVariableName());
    }

    public function testPreviewVariableModifierAlreadyPreviewed()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new DaedalusVariableEvent($daedalus, DaedalusVariableEnum::FUEL, 2, ['test'], $time);
        $event->setAuthor($player)->setIsModified(true);

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

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventModifierService->shouldReceive('applyVariableModifiers')->never();

        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $newEvent = $this->service->computeEventModifications($event, 'eventName');

        $this->assertTrue($newEvent->isModified());
        $this->assertInstanceOf(DaedalusVariableEvent::class, $newEvent);
        $this->assertEquals('eventName', $newEvent->getEventName());
        $this->assertEquals(['test'], $newEvent->getTags());
        $this->assertEquals($time, $newEvent->getTime());
        $this->assertEquals(2, $newEvent->getQuantity());
        $this->assertEquals(DaedalusVariableEnum::FUEL, $newEvent->getVariableName());
    }

    public function testCallVariableModifierAlreadyPreviewed()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new DaedalusVariableEvent($daedalus, DaedalusVariableEnum::FUEL, 2, ['test'], $time);
        $event->setAuthor($player)->setIsModified(true);

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

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventModifierService->shouldReceive('applyVariableModifiers')->never();

        // dispatch the modifier Application
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof ModifierEvent
                && $dispatchedEvent->getTags() === ['test', 'modifierName']
                && $dispatchedEvent->getEventName() === ModifierEvent::APPLY_MODIFIER
                && $eventName === ModifierEvent::APPLY_MODIFIER
                && $dispatchedEvent->getTime() === $time
            ))
            ->once()
        ;
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        // dispatch the event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof DaedalusVariableEvent
                && $dispatchedEvent->getEventName() === 'eventName'
                && $eventName === 'eventName'
                && $dispatchedEvent->getTime() === $time
                && $dispatchedEvent->getQuantity() === $event->getQuantity()
            ))
            ->once()
        ;

        $this->service->callEvent($event, 'eventName');
    }

    public function testCallTriggerModifierNull()
    {
        $time = new \DateTime();
        $daedalus = new Daedalus();
        $place = new Place();
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($place);

        $event = new AbstractGameEvent(['test'], $time);
        $event->setAuthor($player);

        $modifierConfig = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setTriggeredEvent(null)
            ->setReplaceEvent(true)
        ;
        $modifier = new GameModifier($player, $modifierConfig);

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;

        // dispatch the modifier Application
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof ModifierEvent
                && $dispatchedEvent->getTags() === ['test', 'modifierName']
                && $dispatchedEvent->getEventName() === ModifierEvent::APPLY_MODIFIER
                && $eventName === ModifierEvent::APPLY_MODIFIER
                && $dispatchedEvent->getTime() === $time
            ))
            ->once()
        ;
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;

        $this->service->callEvent($event, 'eventName');
    }

    public function testCallTriggerModifierReplace()
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
            ->setReplaceEvent(true)
        ;

        $modifier = new GameModifier($player, $modifierConfig);

        $triggeredEvent = new PlayerCycleEvent(
            $player,
            ['test', 'modifierName', 'triggeredTag'],
            $time
        );
        $triggeredEvent->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;
        $this->eventCreationService->shouldReceive('createEvents')
            ->with($eventConfig, $player, $player, ['test', 'modifierName'], $time)
            ->andReturn([$triggeredEvent])
            ->once()
        ;

        // dispatch the modifier Application
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof ModifierEvent
                && $dispatchedEvent->getEventName() === ModifierEvent::APPLY_MODIFIER
                && $eventName === ModifierEvent::APPLY_MODIFIER
                && $dispatchedEvent->getTime() === $time
            ))
            ->once()
        ;
        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;
        // dispatch the triggered event
        $this->eventDispatcherService->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $dispatchedEvent, string $eventName) => (
                $dispatchedEvent instanceof PlayerCycleEvent
                && $dispatchedEvent->getTags() === $triggeredEvent->getTags()
                && $dispatchedEvent->getEventName() === VariableEventInterface::CHANGE_VARIABLE
                && $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $dispatchedEvent->getTime() === $time
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

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 0)
            ->andReturn(new ModifierCollection([]))
            ->once()
        ;

        $reason = $this->service->eventCancelReason($event, 'eventName');
        $this->assertNull($reason);

        $modifierConfig = new TriggerEventModifierConfig('unitTestTriggerEventModifier');
        $modifierConfig
            ->setModifierName('modifierName')
            ->setTargetEvent('eventName')
            ->setTriggeredEvent(null)
            ->setReplaceEvent(true)
        ;
        $modifier = new GameModifier($player, $modifierConfig);

        $this->modifierRequirementService->shouldReceive('getActiveModifiers')
            ->withArgs(fn (ModifierCollection $modifiers) => $modifiers->count() === 1 && $modifiers->first() === $modifier)
            ->andReturn(new ModifierCollection([$modifier]))
            ->once()
        ;

        $this->eventDispatcherService->shouldReceive('dispatch')->never();

        $reason = $this->service->eventCancelReason($event, 'eventName');

        $this->assertEquals('modifierName', $reason);
    }
}
