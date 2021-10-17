<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Drop;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;

class DropActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::DROP);

        $this->action = new Drop(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
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

    public function testExecute()
    {
        $room = new Place();
        $gameItem = new GameItem();

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $gameItem->setEquipment($item);

        $item
            ->setName('itemName')
        ;

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem
            ->setName('itemName')
            ->setHolder($player)
        ;
        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getEquipments());
        $this->assertCount(1, $room->getEquipments());
    }
}
