<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ExtractSporeActionTest extends AbstractActionTest
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::EXTRACT_SPORE, 2);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new ExtractSpore(
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
        $daedalus->setSpores(1);

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $mushStatus = new ChargeStatus($player, PlayerStatusEnum::MUSH);
        $mushStatus
            ->setCharge(0)
        ;
        $sporeStatus = new ChargeStatus($player, PlayerStatusEnum::SPORES);
        $sporeStatus
            ->setCharge(1)
        ;

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $player->getStatuses());
        $this->assertEquals(2, $player->getStatusByName(PlayerStatusEnum::SPORES)->getCharge());
    }
}
