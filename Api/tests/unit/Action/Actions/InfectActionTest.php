<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InfectActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;

    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Infect(
            $eventDispatcher,
            $this->roomLogService,
            $this->statusService,
            $this->playerService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCannotExecute()
    {
        $daedalus = new Daedalus();

        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setPlayer($targetPlayer);

        $this->action->loadParameters($player, $actionParameter);

        $mushStatus = new ChargeStatus();
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH);

        $sporeStatus = new ChargeStatus();
        $sporeStatus
            ->setCharge(2)
            ->setName(PlayerStatusEnum::SPORES);

        //Player not mush
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //player already daily infected
        $mushStatus->setPlayer($player);
        $sporeStatus->setPlayer($player);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //No spores availlable
        $sporeStatus
            ->setCharge(0);
        $mushStatus
            ->setCharge(1);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //target player is mush
        $sporeStatus
            ->setCharge(1);
        $mushStatus2 = new ChargeStatus();
        $mushStatus2
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH)
            ->setPlayer($targetPlayer);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //target player is immune
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $immune = new Status();
        $immune
            ->setName(PlayerStatusEnum::IMMUNIZED)
            ->setPlayer($targetPlayer);

        $actionParameter->setPlayer($targetPlayer);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();

        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setPlayer($targetPlayer);

        $mushStatus = new ChargeStatus();
        $mushStatus
            ->setCharge(1)
            ->setName(PlayerStatusEnum::MUSH)
            ->setPlayer($player);

        $sporeStatus = new ChargeStatus();
        $sporeStatus
            ->setCharge(1)
            ->setName(PlayerStatusEnum::SPORES)
            ->setPlayer($player);

        $this->action->loadParameters($player, $actionParameter);

        $actionParameter = new ActionParameters();

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->roomLogService->shouldReceive('createPlayerLog')->twice();
        $this->playerService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('delete')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals(0, $player->getStatusByName(PlayerStatusEnum::MUSH)->getCharge());
        $this->assertEquals(9, $player->getActionPoint());
    }

    private function createPlayer(Daedalus $daedalus, Room $room): Player
    {
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setRoom($room)
        ;

        return $player;
    }
}
