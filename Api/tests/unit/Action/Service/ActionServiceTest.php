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
        $action = $this->createAction(1, 0, 0);

        $this->eventService->shouldReceive('callEvent')->times(5);

        $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals(4, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(5, $player->getMoralPoint());

        // movement cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 1, 0);

        $this->eventService->shouldReceive('callEvent')->times(5);

        $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals(5, $player->getActionPoint());
        $this->assertEquals(4, $player->getMovementPoint());
        $this->assertEquals(5, $player->getMoralPoint());

        // moral cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 0, 1);

        $this->eventService->shouldReceive('callEvent')->times(5);

        $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals(5, $player->getActionPoint());
        $this->assertEquals(5, $player->getMovementPoint());
        $this->assertEquals(4, $player->getMoralPoint());

        // mixed cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, 0, 1);

        $this->eventService->shouldReceive('callEvent')->times(6);

        $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals(5, $player->getActionPoint());
        $this->assertEquals(4, $player->getMovementPoint());
        $this->assertEquals(4, $player->getMoralPoint());
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
