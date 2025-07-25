<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\UseBandage;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class UseBandageTest extends AbstractActionTest
{
    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::USE_BANDAGE);
        $this->actionConfig->setOutputQuantity(2);

        $this->actionHandler = new UseBandage(
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
        $gameItem
            ->setName('item')
            ->setEquipment($item);

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer($daedalus, $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::OUTPUT_QUANTITY, $this->actionHandler->getTags())
            ->andReturn(2)
            ->once();
        $this->eventService->shouldReceive('callEvent');
        $this->eventService->shouldReceive('callEvent');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
