<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActionServiceTest extends TestCase
{
    private EventServiceInterface|Mockery\Mock $eventService;

    protected ValidatorInterface|Mockery\Mock $validator;

    protected ActionServiceInterface|Mockery\Mock $actionService;

    private ActionServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = Mockery::mock(EventServiceInterface::class);

        $this->actionService = Mockery::mock(ActionServiceInterface::class);
        $this->validator = Mockery::mock(ValidatorInterface::class);

        $this->service = new ActionService(
            $this->eventService
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

        // movement cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 1, 0);

        $this->eventService->shouldReceive('callEvent')->times(5);

        $this->service->applyCostToPlayer($player, $action, null);

        // moral cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 0, 1);

        $this->eventService->shouldReceive('callEvent')->times(5);

        $this->service->applyCostToPlayer($player, $action, null);

        // mixed cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, 0, 1);

        $this->eventService->shouldReceive('callEvent')->times(6);

        $this->service->applyCostToPlayer($player, $action, null);
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
