<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\WashInSink;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

class WashInSinkActionTest extends AbstractActionTest
{
    /* @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->actionEntity = $this->createActionEntity(ActionEnum::WASH_IN_SINK, 3);

        $this->action = new WashInSink(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $sinkEquipment = new GameEquipment($room);
        $sinkConfig = new EquipmentConfig();
        $sinkEquipment
            ->setEquipment($sinkConfig)
            ->setName('sink')
        ;
        $sinkConfig->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $sinkEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('removeStatus')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }
}
