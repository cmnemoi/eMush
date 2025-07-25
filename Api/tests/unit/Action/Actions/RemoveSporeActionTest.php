<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\RemoveSpore;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class RemoveSporeActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::REMOVE_SPORE, 1);

        $this->actionHandler = new RemoveSpore(
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

        $player = $this->createPlayer($daedalus, $room);
        $player->setSpores(1);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('sporeSucker');

        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->times(2);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
