<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Cook;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class CookActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::COOK, 1);

        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new Cook(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->statusService,
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
        // frozen fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem($player);
        $ration = new EquipmentConfig();
        $ration->setEquipmentName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setName('ration');

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::FROZEN);
        $frozenStatus = new Status($gameRation, $statusConfig);

        $gameKitchen = new GameEquipment($room);
        $kitchen = new ItemConfig();
        $kitchen->setEquipmentName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN);

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('removeStatus')->once();
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->never();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertCount(1, $player->getEquipments());
        self::assertSame($gameRation->getName(), $player->getEquipments()->first()->getName());
        self::assertCount(0, $player->getStatuses());

        $room = new Place();
    }

    public function testExecuteRation()
    {
        $room = new Place();

        // Standard Ration
        $gameRation = new GameItem($room);
        $ration = new ItemConfig();
        $ration->setEquipmentName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setEquipment($ration)
            ->setName(GameRationEnum::STANDARD_RATION);

        $gameKitchen = new GameEquipment($room);
        $kitchen = new EquipmentConfig();
        $kitchen->setEquipmentName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $gameCookedRation = new GameItem(new Place());
        $cookedRation = new ItemConfig();
        $cookedRation
            ->setEquipmentName(GameRationEnum::COOKED_RATION);
        $gameCookedRation
            ->setEquipment($cookedRation)
            ->setName(GameRationEnum::COOKED_RATION);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(2, $room->getEquipments());
        self::assertCount(0, $room->getEquipments()->first()->getStatuses());
        self::assertCount(0, $player->getStatuses());
    }
}
