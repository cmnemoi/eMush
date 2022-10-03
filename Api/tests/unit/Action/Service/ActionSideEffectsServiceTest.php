<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionSideEffectsService;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;
use Mush\Game\Service\EventServiceInterface;

class ActionSideEffectsServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface|Mockery\Mock $eventService;
    /** @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface|Mockery\Mock $roomLogService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface|Mockery\Mock $randomService;

    private ActionSideEffectsServiceInterface $actionService;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = Mockery::mock(EventServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->actionService = new ActionSideEffectsService(
            $this->eventService,
            $this->randomService,
            $this->roomLogService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testHandleActionSideEffect()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setName(ActionEnum::DROP)
        ;

        $date = new \DateTime();

        $this->eventService->shouldReceive('callEvent')->times(2);
        $this->randomService->shouldReceive('getSuccessThreshold')->twice();
        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $this->assertCount(0, $player->getStatuses());
    }

    public function testHandleActionSideEffectDirty()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $date = new \DateTime();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::DROP)
        ;

        $this->eventService->shouldReceive('callEvent')->times(4);
        $this->randomService->shouldReceive('getSuccessThreshold')->twice();

        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $this->assertCount(1, $player->getStatuses());
    }

    public function testHandleActionSideEffectInjury()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);
        $date = new \DateTime();

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(100)
            ->setName(ActionEnum::DROP)
        ;

        $this->eventService->shouldReceive('callEvent')->times(4);
        $this->randomService->shouldReceive('getSuccessThreshold')->twice();

        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $this->assertCount(0, $player->getStatuses());
    }
}
