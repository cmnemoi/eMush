<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shred;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Place\Entity\Place;

class ShredActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SHRED);

        $this->action = new Shred(
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
        $document = new Document();
        $document->setCanShred(true);
        $item->setMechanics(new ArrayCollection([$document]));
        $gameItem
            ->setName('item')
            ->setEquipment($item)
        ;

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getEquipments());
    }
}
