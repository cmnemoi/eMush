<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\WashInSink;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

class WashInSinkActionTest extends AbstractActionTest
{
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->actionEntity = $this->createActionEntity(ActionEnum::WASH_IN_SINK, 3);

        $this->action = new WashInSink(
            $this->eventService,
            $this->actionService,
            $this->validator
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
        $room = new Place();

        $sinkEquipment = new GameEquipment();
        $sinkConfig = new EquipmentConfig();
        $sinkEquipment
            ->setEquipment($sinkConfig)
            ->setHolder($room);
        $sinkConfig->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $sinkEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }
}
