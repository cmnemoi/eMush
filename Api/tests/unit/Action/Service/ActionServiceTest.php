<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActionServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var EventModifierServiceInterface|Mockery\Mock */
    private EventModifierServiceInterface $modifierService;

    /** @var ValidatorInterface|Mockery\Mock */
    protected ValidatorInterface $validator;

    /** @var ActionServiceInterface|Mockery\Mock */
    protected ActionServiceInterface $actionService;

    private ActionServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->modifierService = \Mockery::mock(EventModifierServiceInterface::class);

        $this->actionService = \Mockery::mock(ActionServiceInterface::class);
        $this->validator = \Mockery::mock(ValidatorInterface::class);

        $this->service = new ActionService(
            $this->eventService,
            $this->modifierService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testApplyCostToPlayerActionPoints()
    {
        // ActionPoint
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, 0, 0);

        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::ACTION_POINT, null)
            ->andReturn(1)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null)
            ->andReturn(0)
            ->times(2)
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(0)
            ->once()
        ;

        $eventDispatched = static function (int $delta, string $name) {
            return fn (PlayerVariableEvent $event, string $eventName) => $event->getQuantity() === $delta && $eventName === $name;
        };

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-1, VariableEventInterface::CHANGE_VARIABLE))
            ->once();

        $this->service->applyCostToPlayer($player, $action, null);
    }

    public function testApplyCostToPlayerMovementPoints()
    {
        // movement cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 1, 0);

        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::ACTION_POINT, null)
            ->andReturn(0)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null)
            ->andReturn(1)
            ->times(2)
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(0)
            ->once()
        ;

        $eventDispatched = static function (int $delta, string $name) {
            return fn (PlayerVariableEvent $event, string $eventName) => $event->getQuantity() === $delta && $eventName === $name;
        };

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-1, VariableEventInterface::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);
    }

    public function testApplyCostToPlayerMoralPoints()
    {
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 0, 1);

        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::ACTION_POINT, null)
            ->andReturn(0)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null)
            ->andReturn(0)
            ->times(2)
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(1)
            ->once()
        ;

        $eventDispatched = static function (int $delta, string $name) {
            return fn (PlayerVariableEvent $event, string $eventName) => $event->getQuantity() === $delta && $eventName === $name;
        };

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-1, VariableEventInterface::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);
    }

    public function testApplyCostToPlayerVariousPoints()
    {
        // mixed cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, 0, 1);

        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::ACTION_POINT, null)
            ->andReturn(1)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null)
            ->andReturn(0)
            ->times(2)
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(1)
            ->once()
        ;

        $eventDispatched = static function (int $delta, string $name) {
            return fn (PlayerVariableEvent $event, string $eventName) => $event->getQuantity() === $delta && $eventName === $name;
        };

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                fn (PlayerVariableEvent $event, string $eventName) => (
                    $event->getQuantity() === -1 &&
                    $eventName === VariableEventInterface::CHANGE_VARIABLE)
            )
            ->twice()
        ;

        $this->service->applyCostToPlayer($player, $action, null);
    }

    public function testApplyCostToPlayerActionPointsWithModifiers()
    {
        // ActionPoint with modifiers
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, 0, 0);

        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::ACTION_POINT, null)
            ->andReturn(3)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MOVEMENT_POINT, null)
            ->andReturn(0)
            ->times(2)
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(0)
            ->once()
        ;

        $eventDispatched = static function (int $delta, string $name) {
            return fn (PlayerVariableEvent $event, string $eventName) => $event->getQuantity() === $delta && $eventName === $name;
        };

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-3, VariableEventInterface::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);
    }

    public function testGetSuccessRate()
    {
        $player = $this->createPlayer(5, 5, 5);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt
            ->setAction(ActionEnum::TAKE)
            ->setCharge(0)
        ;

        $action = $this->createAction(0, 1, 0, 20);

        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0)
            ->andReturn(20)
            ->once()
        ;
        $this->assertEquals(20, $this->service->getSuccessRate($action, $player, null));

        // With GameModifier
        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0)
            ->andReturn(40)
            ->once()
        ;
        $this->assertEquals(40, $this->service->getSuccessRate($action, $player, null));

        // With already an attempt
        $attempt->setCharge(1);

        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 1)
            ->andReturn(25)
            ->once()
        ;
        $this->assertEquals(25, $this->service->getSuccessRate($action, $player, null));

        // With 3 attempts
        $attempt->setCharge(3);

        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 3)
            ->andReturn(39)
            ->once()
        ;
        $this->assertEquals(39, $this->service->getSuccessRate($action, $player, null));

        // Attempt + modifier
        $attempt->setCharge(3);

        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 3)
            ->andReturn(78)
            ->once()
        ;
        $this->assertEquals(78, $this->service->getSuccessRate($action, $player, null));

        // More than 99%
        $attempt->setCharge(3);

        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 3)
            ->andReturn(117)
            ->once()
        ;
        $this->assertEquals(99, $this->service->getSuccessRate($action, $player, null));
    }

    private function createPlayer(int $actionPoint, int $movementPoint, int $moralPoint): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setInitActionPoint($actionPoint)
            ->setMaxActionPoint(12)
            ->setInitMoralPoint($moralPoint)
            ->setMaxMoralPoint(12)
            ->setMaxMovementPoint(12)
            ->setInitMovementPoint($movementPoint)
        ;
        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
        ;

        return $player;
    }

    private function createAction(int $actionPointCost, int $movementPointCost, int $moralPointCost, int $successRate = 100): Action
    {
        $action = new Action();

        $action
            ->setActionCost($actionPointCost)
            ->setMoralCost($moralPointCost)
            ->setMovementCost($movementPointCost)
            ->setSuccessRate($successRate)
            ->setActionName(ActionEnum::TAKE)
        ;

        return $action;
    }
}
