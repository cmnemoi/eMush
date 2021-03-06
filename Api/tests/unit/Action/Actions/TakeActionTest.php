<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Take;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class TakeActionTest extends AbstractActionTest
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

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Take(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->playerService,
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
        $room = new Place();
        $gameItem = new GameItem();

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $gameItem->setEquipment($item);
        $gameItem
            ->setName('itemName')
            ->setPlace($room)
        ;

        $item
            ->setIsHeavy(false)
        ;

        $gameConfig = new GameConfig();
        $gameConfig->setMaxItemInInventory(3);

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $room);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($room->getEquipments());
        $this->assertCount(1, $player->getItems());
    }
}
