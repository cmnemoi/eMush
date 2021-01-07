<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusServiceInterface;

    private ActionServiceInterface $actionService;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusServiceInterface = Mockery::mock(StatusServiceInterface::class);

        $this->actionService = new ActionService(
            $this->eventDispatcher,
            $this->randomService,
            $this->statusServiceInterface,
            $this->roomLogService
        );
    }

    public function testHandleActionSideEffectDirty()
    {
        $action = new Action();
        $room = new Room();
        $player = new Player();
        $player->setRoom($room);

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(0)
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());

        $action->setDirtyRate(100);

        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $this->statusServiceInterface->shouldReceive('createCorePlayerStatus')->andReturn(new Status())->once();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(1, $player->getStatuses());
    }

    public function testHandleActionSideEffectInjury()
    {
        $action = new Action();
        $room = new Room();
        $player = new Player();
        $player->setRoom($room);

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(0)
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());

        $action->setInjuryRate(100);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(
                fn (PlayerEvent $playerEvent, string $eventName) => ($playerEvent->getActionModifier()->getHealthPointModifier() === -2)
            )
            ->once()
        ;
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $this->statusServiceInterface->shouldReceive('createCorePlayerStatus')->never();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());
    }
}
