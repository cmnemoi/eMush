<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Infect;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class InfectActionTest extends AbstractActionTest
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INFECT, 1);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);

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

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $mushStatus
            ->setCharge(1)
        ;

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig->setName(PlayerStatusEnum::SPORES);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus
            ->setCharge(1)
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
