<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InfectActionTest extends AbstractActionTest
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;

    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INFECT, 1);

        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Infect(
            $this->eventDispatcher,
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

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

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

        $player
            ->addStatus($mushStatus)
            ->addStatus($sporeStatus)
        ;

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
        ;

        $targetPlayer
            ->addStatus($mushStatus2)
        ;

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //target player is immune
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $immune = new Status();
        $immune
            ->setName(PlayerStatusEnum::IMMUNIZED)
        ;

        $targetPlayer
            ->addStatus($immune)
        ;

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
        ;

        $sporeStatus = new ChargeStatus();
        $sporeStatus
            ->setCharge(1)
            ->setName(PlayerStatusEnum::SPORES)
        ;

        $player
            ->addStatus($mushStatus)
            ->addStatus($sporeStatus)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->playerService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('delete')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals(0, $player->getStatusByName(PlayerStatusEnum::MUSH)->getCharge());
        $this->assertEquals(9, $player->getActionPoint());
    }
}
