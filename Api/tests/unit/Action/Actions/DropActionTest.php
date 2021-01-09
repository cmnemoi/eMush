<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Drop;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Service\StatusServiceInterface;

class DropActionTest extends AbstractActionTest
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

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::DROP);

        $this->action = new Drop(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->statusService
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
        $room = new Room();
        $gameItem = new GameItem();

        $dropAction = new Action();
        $dropAction->setName(ActionEnum::DROP);

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$dropAction]));

        $gameItem->setEquipment($item);

        $item
            ->setName('itemName')
            ->setIsHeavy(false)
        ;

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem
            ->setName('itemName')
            ->setPlayer($player)
        ;
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertCount(1, $room->getEquipments());

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertCount(1, $room->getEquipments());
    }
}
