<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class TakeActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::TRANSPLANT);

        $this->actionHandler = new Take(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
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
        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $gameItem->setEquipment($item);
        $gameItem
            ->setName('itemName');

        $player = $this->createPlayer($daedalus, $room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }

    public function testTakeHeavyObject()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);

        $item = new ItemConfig();
        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $gameItem->setEquipment($item);
        $gameItem
            ->setName('itemName');

        $player = $this->createPlayer($daedalus, $room);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
