<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\UpdateTalkie;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class UpdateTalkieActionTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::UPDATE_TALKIE);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionHandler = new UpdateTalkie(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecuteRation()
    {
        // Standard Ration
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $talkie = new GameItem($player);
        $talkie
            ->setName(ItemEnum::WALKIE_TALKIE);

        $tracker = new GameItem($player);
        $tracker
            ->setName(ItemEnum::TRACKER);

        $neronCore = new GameEquipment($room);
        $neronCore
            ->setName(EquipmentEnum::NERON_CORE)
            ->setHolder($room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $talkie);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
