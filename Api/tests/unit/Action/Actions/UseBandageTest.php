<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\UseBandage;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

class UseBandageTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::USE_BANDAGE);

        $this->action = new UseBandage(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem
            ->setName('item')
            ->setEquipment($item)
        ;

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
