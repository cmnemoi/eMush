<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\GetUp;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class GetUpActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::GET_UP);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new GetUp(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
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
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameEquipment($room);
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::BED);
        $gameItem
            ->setEquipment($item)
            ->setName(EquipmentEnum::BED);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::LYING_DOWN);
        $status = new Status($player, $statusConfig);
        $status
            ->setTarget($gameItem);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('removeStatus')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertSame(10, $player->getActionPoint());
    }
}
