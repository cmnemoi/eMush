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
        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(1, null, null)));

        //movement cost
        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(null, 1, null)));

        //moral cost
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(null, null, 1)));

        //mixed cost
        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(1, 1, 1)));

        //With pa pm conversion
        $player = $this->createPlayer(1, 0, 0);
        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(null, 1, null)));

        $player = $this->createPlayer(0, 0, 0);
        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(1, null, null)));

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(0)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(null, 1, null)));

        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(null, null, 1)));

        //With modifiers
        $player = $this->createPlayer(1, 0, 0);

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(-2)
            ->once()
        ;
        $this->assertTrue($this->service->canPlayerDoAction($player, $this->createAction(3, null, null)));

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(-1)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(3, null, null)));

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(2)
            ->once()
        ;
        $this->assertFalse($this->service->canPlayerDoAction($player, $this->createAction(1, null, null)));
    }

    public function testApplyCostToPlayer()
    {
        //ActionPoint
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, null);

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(0)
            ->once()
        ;

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(4, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(5, $player->getMoralPoint());

        //movement cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(null, 1, null);

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::MOVEMENT_POINT)
            ->andReturn(0)
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

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(0)
            ->once()
        ;

        $player = $this->service->applyCostToPlayer($player, $action);
        $this->assertEquals(4, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(4, $player->getMoralPoint());

        //ActionPoint with modifiers
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, null, null);

        $this->actionModifierService->shouldReceive('getAdditiveModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::ACTION_POINT)
            ->andReturn(2)
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

        $this->actionModifierService->shouldReceive('getMultiplicativeModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(1)
            ->once()
        ;
        $this->assertEquals(20, $this->service->getSuccessRate($action, $player, 20));

        //With Modifier
        $this->actionModifierService->shouldReceive('getMultiplicativeModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(2)
            ->once()
        ;
        $this->assertEquals(40, $this->service->getSuccessRate($action, $player, 20));

        //With already an attempt
        $attempt->setCharge(1);

        $this->actionModifierService->shouldReceive('getMultiplicativeModifier')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(1)
            ->once()
        ;
        $this->assertEquals(25, $this->service->getSuccessRate($action, $player, 20));
    }

    public function testGetAttempt()
    {
        $player = $this->createPlayer(5, 5, 5);

        //no Attemps status
        $this->statusService->shouldReceive('createAttemptStatus')->once();
        $answer = $this->service->getAttempt($player, ActionEnum::REPAIR);

        $attempt = new Attempt($player);
        $attempt
            ->setAction(ActionEnum::REPAIR)
            ->setName(StatusEnum::ATTEMPT)
            ->setCharge(1)
        ;
        //not the same action
        $answer = $this->service->getAttempt($player, ActionEnum::TAKE);

        $this->assertInstanceOf(Attempt::class, $answer);
        $this->assertEquals(0, $attempt->getCharge());
        $this->assertEquals(ActionEnum::TAKE, $attempt->getAction());

        //same action
        $player = $this->createPlayer(5, 5, 5);
        $attempt = new Attempt($player);
        $attempt
            ->setAction(ActionEnum::TAKE)
            ->setName(StatusEnum::ATTEMPT)
            ->setCharge(1)
        ;
        $answer = $this->service->getAttempt($player, ActionEnum::TAKE);
        $this->assertInstanceOf(Attempt::class, $answer);
        $this->assertEquals(1, $answer->getCharge());
        $this->assertEquals(ActionEnum::TAKE, $answer->getAction());
    }

    public function testSuccessRateFormula()
    {
        $keyModificator = 1.5;
        $technician = 2;
        $expert = 0.2;

        //Base 6
        $this->assertEquals(6, $this->service->computeSuccessRate(6, 0, 1));
        $this->assertEquals(11, $this->service->computeSuccessRate(6, 3, 1));
        $this->assertEquals(18, $this->service->computeSuccessRate(6, 5, 1));
        //Base 25
        $this->assertEquals(25, $this->service->computeSuccessRate(25, 0, 1));
        $this->assertEquals(48, $this->service->computeSuccessRate(25, 3, 1));
        $this->assertEquals(76, $this->service->computeSuccessRate(25, 5, 1));

        //Modificator like adjustable wrench
        //Base 6
        $this->assertEquals(9, $this->service->computeSuccessRate(6, 0, $keyModificator));
        $this->assertEquals(17, $this->service->computeSuccessRate(6, 3, $keyModificator));
        $this->assertEquals(27, $this->service->computeSuccessRate(6, 5, $keyModificator));
        //Base 25
        $this->assertEquals(37, $this->service->computeSuccessRate(25, 0, $keyModificator));
        $this->assertEquals(73, $this->service->computeSuccessRate(25, 3, $keyModificator));
        $this->assertEquals(99, $this->service->computeSuccessRate(25, 5, $keyModificator));

        //Modificator with 3 adjustable wrench
        $this->assertEquals(37, $this->service->computeSuccessRate(25, 0, $keyModificator));
        $this->assertEquals(56, $this->service->computeSuccessRate(25, 0, $keyModificator ** 2));
        $this->assertEquals(84, $this->service->computeSuccessRate(25, 0, $keyModificator ** 3));

        //Technician Modificator with 3 adjustable wrench

        $this->assertEquals(30, $this->service->computeSuccessRate(25, 0, 1, $expert));
        $this->assertEquals(55, $this->service->computeSuccessRate(25, 0, $technician, $expert));
        $this->assertEquals(42, $this->service->computeSuccessRate(25, 0, $keyModificator ** 1, $expert));
        $this->assertEquals(61, $this->service->computeSuccessRate(25, 0, $keyModificator ** 2, $expert));
        $this->assertEquals(89, $this->service->computeSuccessRate(25, 0, $keyModificator ** 3, $expert));
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
