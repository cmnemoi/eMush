<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActionServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;

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
        $this->eventService = Mockery::mock(EventServiceInterface::class);
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->actionService = Mockery::mock(ActionServiceInterface::class);
        $this->validator = Mockery::mock(ValidatorInterface::class);

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
        Mockery::close();
    }

    public function testApplyCostToPlayer()
    {
        // ActionPoint
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, null);

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
            ->once()
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
            ->withArgs($eventDispatched(-1, AbstractQuantityEvent::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);

        // movement cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(null, 1, null);

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
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(0)
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-1, AbstractQuantityEvent::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);

        // moral cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(null, null, 1);

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
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(1)
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-1, AbstractQuantityEvent::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);

        // mixed cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, 1);

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
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(1)
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                fn (PlayerVariableEvent $event, string $eventName) => (
                    $event->getQuantity() === -1 &&
                    $eventName === AbstractQuantityEvent::CHANGE_VARIABLE)
            )
            ->twice()
        ;

        $this->service->applyCostToPlayer($player, $action, null);

        // ActionPoint with modifiers
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, null);

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
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('getActionModifiedValue')
            ->with($action, $player, PlayerVariableEnum::MORAL_POINT, null)
            ->andReturn(0)
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs($eventDispatched(-3, AbstractQuantityEvent::CHANGE_VARIABLE))
            ->once()
        ;

        $this->service->applyCostToPlayer($player, $action, null);
    }

    public function testGetSuccessRate()
    {
        $player = $this->createPlayer(5, 5, 5);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setName(StatusEnum::ATTEMPT);
        $attempt = new Attempt($player, $statusConfig);
        $attempt
            ->setAction(ActionEnum::TAKE)
            ->setCharge(0)
        ;

        $action = $this->createAction(null, 1, null);

        $action->setSuccessRate(20);

        $this->modifierService->shouldReceive('getActionModifiedValue')
            ->with($action, $player, ModifierTargetEnum::PERCENTAGE, null, 0)
            ->andReturn(20)
            ->once()
        ;
        $this->assertEquals(20, $this->service->getSuccessRate($action, $player, null));

        // With Modifier
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
        $player = new Player();
        $player
            ->setActionPoint($actionPoint)
            ->setMovementPoint($movementPoint)
            ->setMoralPoint($moralPoint)
        ;

        return $player;
    }

    private function createAction(?int $actionPointCost, ?int $movementPointCost, ?int $moralPointCost): Action
    {
        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost($actionPointCost)
            ->setMovementPointCost($movementPointCost)
            ->setMoralPointCost($moralPointCost)
        ;

        $action = new Action();

        $action
            ->setName(ActionEnum::TAKE)
            ->setActionCost($actionCost)
        ;

        return $action;
    }
}
