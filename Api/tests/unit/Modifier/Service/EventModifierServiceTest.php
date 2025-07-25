<?php

namespace Mush\Tests\unit\Modifier\Service;

use Mockery;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierPriorityEnum;
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

/**
 * @internal
 */
final class EventModifierServiceTest extends TestCase
{
    private EventModifierService $service;

    /** @var Mockery\Mock|ModifierHandlerServiceInterface */
    private ModifierHandlerServiceInterface $modifierHandlerService;

    /** @var Mockery\Mock|ModifierRequirementServiceInterface */
    private ModifierRequirementServiceInterface $modifierRequirementService;

    /**
     * @before
     */
    protected function setUp(): void
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
    protected function tearDown(): void
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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::BUILD)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_SHOOT])
            ->setMovementCost(1)
            ->setSuccessRate(50);

        // add attempt
        $event = new ActionVariableEvent($action, $player, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, [], null);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt->setCharge(1);
        $attempt->setAction(ActionEnum::BUILD->value);

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);

        self::assertInstanceOf(EventChain::class, $modifiedEvents);
        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertInstanceOf(VariableEventInterface::class, $modifiedEvent);
        self::assertSame(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        self::assertSame(50 * 1.25 ** 1, $modifiedEvent->getQuantity());

        // More attempts
        $event = new ActionVariableEvent($action, $player, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, [], null);
        $attempt->setCharge(3);

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);

        self::assertInstanceOf(EventChain::class, $modifiedEvents);
        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertInstanceOf(VariableEventInterface::class, $modifiedEvent);
        self::assertSame(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        self::assertSame(50 * 1.25 ** 3, $modifiedEvent->getQuantity());
    }

    public function testComputeAttemptIncreaseWrongAction()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player = new Player();
        $player->setDaedalus($daedalus)->setPlace($room);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::BUILD)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_SHOOT])
            ->setMovementCost(1)
            ->setSuccessRate(50);

        // add attempt
        $event = new ActionVariableEvent($action, $player, ActionVariableEnum::PERCENTAGE_SUCCESS, 50, $player, [], null);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt->setCharge(1);
        $attempt->setAction(ActionEnum::ATTACK->value);

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);

        self::assertInstanceOf(EventChain::class, $modifiedEvents);
        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertInstanceOf(VariableEventInterface::class, $modifiedEvent);
        self::assertSame(ActionVariableEnum::PERCENTAGE_SUCCESS, $modifiedEvent->getVariableName());
        self::assertSame(50.0, $modifiedEvent->getQuantity());
    }

    public function testApplyOneModifier()
    {
        $daedalus = DaedalusFactory::createDaedalus();
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
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY);
        $modifier = new GameModifier($daedalus, $modifierConfig);
        $modifier->setModifierProvider($player);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->withArgs(static fn (ModifierActivationRequirementCollection $requirement) => $requirement->isEmpty())
            ->andReturn(true)
            ->once();

        $modifierHandler = $this->createMock(AbstractModifierHandler::class);
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->andReturn($modifierHandler)
            ->once();

        $modifierHandler
            ->method('handleEventModifier')
            ->willReturn(new EventChain([$event]));

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);

        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertInstanceOf(DaedalusEvent::class, $modifiedEvent);
        self::assertContains('testEventModifierConfig', $modifiedEvent->getTags());
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
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY);
        $modifier = new GameModifier($daedalus, $modifierConfig);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($modifierConfig->getModifierActivationRequirements(), $daedalus)
            ->never();
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->never();

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);
        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertSame($modifiedEvent, $event);
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
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY);
        $modifier = new GameModifier($daedalus, $modifierConfig);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($modifierConfig->getModifierActivationRequirements(), $daedalus)
            ->never();
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->never();

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);
        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertSame($modifiedEvent, $event);
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
            ->setTagConstraints(['tag1' => ModifierRequirementEnum::ANY_TAGS]);
        $modifier = new GameModifier($daedalus, $modifierConfig);
        $modifier->setModifierProvider($player);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->withArgs(static fn (ModifierActivationRequirementCollection $requirement) => $requirement->isEmpty())
            ->andReturn(false)
            ->once();
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier)
            ->never();

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::PRE_MODIFICATION);
        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertSame($modifiedEvent, $event);
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
            ->setPriority(ModifierPriorityEnum::INITIAL_SET_VALUE);
        $modifier1 = new GameModifier($daedalus, $modifierConfig1);
        $modifier1->setModifierProvider($player);

        $modifierConfig2 = new EventModifierConfig('testEventModifierConfig2');
        $modifierConfig2
            ->setTargetEvent('eventName')
            ->setPriority(ModifierPriorityEnum::OVERRIDE_VALUE_PRIORITY);
        $modifier2 = new GameModifier($daedalus, $modifierConfig2);
        $modifier2->setModifierProvider($player);

        // Modifier1 should be applied first
        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->andReturn(true)
            ->twice();
        $modifierHandler1 = $this->createMock(AbstractModifierHandler::class);
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier1)
            ->andReturn($modifierHandler1)
            ->once();
        $firstEventChain = new EventChain([$event, new AbstractGameEvent([], $time)]);
        $modifierHandler1
            ->method('handleEventModifier')
            ->willReturn($firstEventChain);

        // Now applies modifier2
        $modifierHandler2 = $this->createMock(AbstractModifierHandler::class);
        $this->modifierHandlerService
            ->shouldReceive('getModifierHandler')
            ->with($modifier2)
            ->andReturn($modifierHandler2)
            ->once();
        $modifierHandler2
            ->method('handleEventModifier')
            ->willReturn(new EventChain([$event]));

        $modifiedEvents = $this->service->applyModifiers($event, ModifierPriorityEnum::SIMULTANEOUS_MODIFICATION);

        self::assertCount(1, $modifiedEvents);
        $modifiedEvent = $modifiedEvents->first();
        self::assertInstanceOf(DaedalusEvent::class, $modifiedEvent);
        self::assertContains('testEventModifierConfig1', $modifiedEvent->getTags());
        self::assertContains('testEventModifierConfig2', $modifiedEvent->getTags());
    }
}
