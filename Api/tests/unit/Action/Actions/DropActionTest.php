<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Drop;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Item;
use Mush\Place\Entity\Place;

class DropActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::DROP);

        $this->action = new Drop(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
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
        $gameItem = new Item();

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $gameItem->setConfig($item);

        $item
            ->setName('itemName')
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem
            ->setName('itemName')
            ->setHolder($player)
        ;
        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
