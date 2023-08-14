<?php

namespace Mush\Test\Modifier\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierHandler\AbstractModifierHandler;
use Mush\Modifier\Service\EventModifierService;
use Mush\Modifier\Service\ModifierHandlerServiceInterface;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class ModifierServiceTest extends TestCase
{
    private EventModifierService $service;

    /** @var ModifierHandlerServiceInterface|Mockery\Mock */
    private ModifierHandlerServiceInterface $modifierHandlerService;

    /** @var ModifierRequirementServiceInterface|Mockery\Mock */
    private ModifierRequirementServiceInterface $modifierRequirementService;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierHandlerService = \Mockery::mock(ModifierHandlerServiceInterface::class);
        $this->modifierRequirementService = \Mockery::mock(ModifierRequirementServiceInterface::class);

        $this->service = new EventModifierService(
            $this->modifierHandlerService,
            $this->modifierRequirementService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testComputeAttemptIncrease()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setMovementCost(1)
            ->setSuccessRate(50)
        ;

        // add attempt
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt->setCharge(1);
        $attempt->setAction('action');

        $modifiedEvents = $this->service->applyModifiers($event);

        $this->assertInstanceOf(EventChain::class, $modifiedEvents);
        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertInstanceOf(VariableEventInterface::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(50 * 1.25 ** 1, $modifiedEvent->getQuantity());

        // More attempts
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $attempt->setCharge(3);

        $modifiedEvents = $this->service->applyModifiers($event);

        $this->assertInstanceOf(EventChain::class, $modifiedEvents);
        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertInstanceOf(VariableEventInterface::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(50 * 1.25 ** 3, $modifiedEvent->getQuantity());
    }

    public function testComputeAttemptIncreaseWrongAction()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action = new Action();
        $action
            ->setActionName('action')
            ->setTypes(['type1', 'type2'])
            ->setMovementCost(1)
            ->setSuccessRate(50)
        ;

        // add attempt
        $event = new ActionVariableEvent($action, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, null);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt->setCharge(1);
        $attempt->setAction('otherAction');

        $modifiedEvents = $this->service->applyModifiers($event);

        $this->assertInstanceOf(EventChain::class, $modifiedEvents);
        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertInstanceOf(VariableEventInterface::class, $modifiedEvent);
        $this->assertEquals(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        $this->assertEquals(50, $modifiedEvent->getQuantity());
    }

    public function testApplyOneModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $time = new \DateTime();
        $event = new DaedalusEvent($daedalus, ['tag1', 'tag2'], $time);
        $event->setEventName('eventName');

        $modifierConfig = new EventModifierConfig('testEventModifierConfig');
        $modifierConfig
            ->setTargetEvent('eventName')
        ;
        $modifier = new GameModifier($daedalus, $modifierConfig);

        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifier)
            ->andReturn(true)
            ->once()
        ;

        $modifierHandler = $this->createMock(AbstractModifierHandler::class);
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->andReturn($modifierHandler)
            ->once()
        ;

        $modifierHandler
            ->method('handleEventModifier')
            ->willReturn(new EventChain([$event]))
        ;

        $modifiedEvents = $this->service->applyModifiers($event);

        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertInstanceOf(DaedalusEvent::class, $modifiedEvent);
        $this->assertContains('testEventModifierConfig', $modifiedEvent->getTags());
        $this->assertTrue($modifiedEvent->isModified());
    }

    public function testModifierNonRelevantTargetEvent()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $time = new \DateTime();
        $event = new DaedalusEvent($daedalus, ['tag1', 'tag2'], $time);
        $event->setEventName('eventName');

        $modifierConfig = new EventModifierConfig('testEventModifierConfig');
        $modifierConfig
            ->setTargetEvent('otherEventName')
        ;
        $modifier = new GameModifier($daedalus, $modifierConfig);

        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifier)
            ->never()
        ;
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->never()
        ;

        $modifiedEvents = $this->service->applyModifiers($event);
        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertEquals($modifiedEvent, $event);
    }

    public function testModifierNonRelevantTag()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $time = new \DateTime();
        $event = new DaedalusEvent($daedalus, ['tag1', 'tag2'], $time);
        $event->setEventName('eventName');

        $modifierConfig = new EventModifierConfig('testEventModifierConfig');
        $modifierConfig
            ->setTargetEvent('eventName')
            ->setTagConstraints(['tag1' => ModifierRequirementEnum::NONE_TAGS])
        ;
        $modifier = new GameModifier($daedalus, $modifierConfig);

        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifier)
            ->never()
        ;
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->never()
        ;

        $modifiedEvents = $this->service->applyModifiers($event);
        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertEquals($modifiedEvent, $event);
    }

    public function testModifierNonRelevantModifierRequirement()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $time = new \DateTime();
        $event = new DaedalusEvent($daedalus, ['tag1', 'tag2'], $time);
        $event->setEventName('eventName');

        $modifierConfig = new EventModifierConfig('testEventModifierConfig');
        $modifierConfig
            ->setTargetEvent('eventName')
            ->setTagConstraints(['tag1' => ModifierRequirementEnum::ANY_TAGS])
        ;
        $modifier = new GameModifier($daedalus, $modifierConfig);

        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifier)
            ->andReturn(false)
            ->once()
        ;
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->never()
        ;

        $modifiedEvents = $this->service->applyModifiers($event);
        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertEquals($modifiedEvent, $event);
    }

    public function testApplyTwoModifiers()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $time = new \DateTime();
        $event = new DaedalusEvent($daedalus, ['tag1', 'tag2'], $time);
        $event->setEventName('eventName');

        $modifierConfig1 = new EventModifierConfig('testEventModifierConfig1');
        $modifierConfig1
            ->setTargetEvent('eventName')
            ->setPriority(-3)
        ;
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);

        $modifierConfig2 = new EventModifierConfig('testEventModifierConfig2');
        $modifierConfig2
            ->setTargetEvent('eventName')
            ->setPriority(-1)
        ;
        $modifier2 = new GameModifier($daedalus, $modifierConfig2);

        // Modifier1 should be applied first
        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifier1)
            ->andReturn(true)
            ->once()
        ;
        $modifierHandler1 = $this->createMock(AbstractModifierHandler::class);
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier1)
            ->andReturn($modifierHandler1)
            ->once()
        ;
        $firstEventChain = new EventChain([$event, new AbstractGameEvent([], $time)]);
        $modifierHandler1
            ->method('handleEventModifier')
            ->willReturn($firstEventChain)
        ;

        // Now applies modifier2
        $this->modifierRequirementService
            ->shouldReceive('checkModifier')
            ->with($modifier2)
            ->andReturn(true)
            ->once()
        ;
        $modifierHandler2 = $this->createMock(AbstractModifierHandler::class);
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier2)
            ->andReturn($modifierHandler2)
            ->once()
        ;
        $firstEventChain = new EventChain([$event, new AbstractGameEvent([], $time)]);
        $modifierHandler2
            ->method('handleEventModifier')
            ->willReturn(new EventChain([$event]))
        ;

        $modifiedEvents = $this->service->applyModifiers($event);

        $this->assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        $this->assertInstanceOf(DaedalusEvent::class, $modifiedEvent);
        $this->assertContains('testEventModifierConfig1', $modifiedEvent->getTags());
        $this->assertContains('testEventModifierConfig2', $modifiedEvent->getTags());
        $this->assertTrue($modifiedEvent->isModified());
    }
}
