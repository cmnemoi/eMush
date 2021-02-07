<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InfectActionTest extends AbstractActionTest
{
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

        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Infect(
            $this->eventDispatcher,
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

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setPlayer($targetPlayer);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $mushStatus = new ChargeStatus($player);
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH);

        $sporeStatus = new ChargeStatus($player);
        $sporeStatus
            ->setCharge(2)
            ->setName(PlayerStatusEnum::SPORES);

        //Player not mush
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

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
        $mushStatus2 = new ChargeStatus($targetPlayer);
        $mushStatus2
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH)
        ;

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //target player is immune
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $immune = new Status($targetPlayer);
        $immune
            ->setName(PlayerStatusEnum::IMMUNIZED)
        ;

        $actionParameter->setPlayer($targetPlayer);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setPlayer($targetPlayer);

        $mushStatus = new ChargeStatus($player);
        $mushStatus
            ->setCharge(1)
            ->setName(PlayerStatusEnum::MUSH)
        ;

        $sporeStatus = new ChargeStatus($player);
        $sporeStatus
            ->setCharge(1)
            ->setName(PlayerStatusEnum::SPORES)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->statusService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $player->getStatuses());
        $this->assertEquals(0, $player->getStatusByName(PlayerStatusEnum::MUSH)->getCharge());
        $this->assertEquals(9, $player->getActionPoint());
    }
}
