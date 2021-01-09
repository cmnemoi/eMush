<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Service\StatusServiceInterface;

class TakeActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->action = new Take(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $gameConfigService,
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

        $takeAction = new Action();
        $takeAction->setName(ActionEnum::TAKE);

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$takeAction]));

        $gameItem->setEquipment($item);
        $gameItem
            ->setName('itemName')
            ->setRoom($room)
        ;

        $item
            ->setIsHeavy(false)
        ;

        $this->gameConfig->setMaxItemInInventory(3);
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($room->getEquipments());
        $this->assertCount(1, $player->getItems());

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEmpty($room->getEquipments());
        $this->assertCount(1, $player->getItems());
    }
}
