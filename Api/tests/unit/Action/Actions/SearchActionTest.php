<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Search;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class SearchActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SEARCH, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Search(
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

    public function testExecuteNoItem()
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        // No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();
        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteNoHiddenItem()
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        // No hidden item in the room
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setHolder($room)
        ;
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();
        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        // Success find
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setHolder($room)
        ;

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setName(EquipmentStatusEnum::HIDDEN);
        $hidden = new Status($gameItem, $hiddenConfig);
        $hiddenBy = $this->createPlayer(new Daedalus(), new Place());
        $hidden
            ->setTarget($hiddenBy)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('getMostRecent')->andReturn($gameItem)->once();
        $this->gameEquipmentService->shouldReceive('persist');
        $this->statusService->shouldReceive('delete');
        $this->eventService->shouldReceive('dispatch')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertCount(0, $hiddenBy->getStatuses());
    }

    public function testExecuteTwoHiddenItems()
    {
        // 2 hidden items
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setHolder($room)
        ;

        $hiddenBy = $this->createPlayer(new Daedalus(), new Place());

        $hiddenConfig = new StatusConfig();
        $hiddenConfig->setName(EquipmentStatusEnum::HIDDEN);
        $hidden = new Status($gameItem, $hiddenConfig);
        $hidden
            ->setTarget($hiddenBy)
        ;

        $gameItem2 = new GameItem();
        $gameItem2
            ->setEquipment($item)
            ->setHolder($room)
        ;

        $hidden2 = new Status($gameItem2, $hiddenConfig);
        $hidden2
            ->setTarget($hiddenBy)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('getMostRecent')->andReturn($gameItem)->once();
        $this->eventService->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(1, $room->getEquipments()->last()->getStatuses());
    }
}
