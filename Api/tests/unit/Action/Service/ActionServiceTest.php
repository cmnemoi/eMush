<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionService;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class ActionServiceTest extends TestCase
{
    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->actionModifierService = Mockery::mock(ActionModifierServiceInterface::class);

        $this->service = new ActionService(
            $this->actionModifierService,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCanPlayerDoAction()
    {
        $player = $this->createPlayer(5, 5, 5);

        //action cost
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(1, null, null)));

        //movement cost
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(null, 1, null)));

        //moral cost
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(null, null, 1)));

        //mixed cost
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(1, 1, 1)));

        //With pa pm conversion
        $player = $this->createPlayer(1, 0, 0);
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(null, 1, null)));

        $player = $this->createPlayer(0, 0, 0);
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(1, null, null)));

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(null, 1, null)));

        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(null, null, 1)));

        //With modifiers
        $player = $this->createPlayer(1, 0, 0);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(3, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(1)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(3, null, null)));

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(3, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(2)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(3, null, null)));

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(3)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(1, null, null)));
    }

    public function testApplyCostToPlayer()
    {
        //ActionPoint
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, null);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(1)
            ->once()
        ;

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(4, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(5, $player->getMoralPoint());

        //movement cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(null, 1, null);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(1)
            ->once()
        ;

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(5, $player->getActionPoint());
        $this->assertEquals(4, $player->getMovementPoint());
        $this->assertEquals(5, $player->getMoralPoint());

        //moral cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(null, null, 1);

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(5, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(4, $player->getMoralPoint());

        //mixed cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, 1);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(1)
            ->once()
        ;

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(4, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(4, $player->getMoralPoint());

        //ActionPoint with modifiers
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, null);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1, $player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(3)
            ->once()
        ;

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(2, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(5, $player->getMoralPoint());
    }

    public function testGetSuccessRate()
    {
        $player = $this->createPlayer(5, 5, 5);

        $attempt = new Attempt($player);
        $attempt
            ->setAction(ActionEnum::TAKE)
            ->setName(StatusEnum::ATTEMPT)
            ->setCharge(0)
        ;

        $action = $this->createAction(null, 1, null);

        $action->setSuccessRate(20);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(20)
            ->once()
        ;
        $this->assertEquals(20, $this->service->getSuccessRate($action, $player));

        //With Modifier
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(40)
            ->once()
        ;
        $this->assertEquals(40, $this->service->getSuccessRate($action, $player));

        //With already an attempt
        $attempt->setCharge(1);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(25, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(25)
            ->once()
        ;
        $this->assertEquals(25, $this->service->getSuccessRate($action, $player));

        //With already an attempt
        $attempt->setCharge(3);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1.25 ** 3 * 20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(39)
            ->once()
        ;
        $this->assertEquals(39, $this->service->getSuccessRate($action, $player));

        //Attempt + modifier
        $attempt->setCharge(3);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1.25 ** 3 * 20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(78)
            ->once()
        ;
        $this->assertEquals(78, $this->service->getSuccessRate($action, $player));

        //More than 99%
        $attempt->setCharge(3);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(1.25 ** 3 * 20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(117)
            ->once()
        ;
        $this->assertEquals(99, $this->service->getSuccessRate($action, $player));
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
