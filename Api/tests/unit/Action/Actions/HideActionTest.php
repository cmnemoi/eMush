<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Hide;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Service\StatusServiceInterface;

class HideActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HIDE, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Hide(
            $this->eventDispatcher,
            $this->gameEquipmentService,
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
        $room = new Room();

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setIsHideable(true);
        $gameItem
            ->setEquipment($item)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //item is not in the room
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //item is not hideable
        $gameItem->setRoom($room);
        $item->setIsHideable(false);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setIsHideable(true);
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setPlayer($player)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('createCoreStatus')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getItems());
        $this->assertEquals(9, $player->getActionPoint());
    }
}
