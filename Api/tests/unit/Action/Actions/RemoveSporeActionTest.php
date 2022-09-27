<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RemoveSpore;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RemoveSporeActionTest extends AbstractActionTest
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REMOVE_SPORE, 1);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new RemoveSpore(
            $this->eventService,
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

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setHolder($room)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig->setName(PlayerStatusEnum::SPORES);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus
            ->setCharge(1)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();
        $this->statusService->shouldReceive('updateCharge')->with($sporeStatus, -1)->andReturn($sporeStatus)->once();
        $this->statusService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
