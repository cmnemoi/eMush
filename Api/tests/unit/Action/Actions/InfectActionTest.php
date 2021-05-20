<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Infect;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

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
            $this->actionService,
            $this->validator,
            $this->statusService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

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

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->statusService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $player->getStatuses());
        $this->assertEquals(0, $player->getStatusByName(PlayerStatusEnum::MUSH)->getCharge());
    }
}
