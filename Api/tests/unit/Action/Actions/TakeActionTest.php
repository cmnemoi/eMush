<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Take;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

class TakeActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT);

        $this->action = new Take(
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
        $gameItem = new GameItem($room);

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $gameItem->setEquipment($item);
        $gameItem
            ->setName('itemName')
        ;

        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }

    public function testTakeHeavyObject()
    {
        $room = new Place();
        $gameItem = new GameItem($room);

        $item = new ItemConfig();
        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $gameItem->setEquipment($item);
        $gameItem
            ->setName('itemName')
        ;

        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
