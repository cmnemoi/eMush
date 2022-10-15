<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ExpressCook;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class ExpressCookActionTest extends AbstractActionTest
{
    /* @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::EXPRESS_COOK);

        $this->action = new ExpressCook(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecuteFruit()
    {
        // frozen fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setHolder($player)
            ->setName('ration')
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::FROZEN);
        $frozenStatus = new Status($gameRation, $statusConfig);

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setHolder($room)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->never();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getEquipments());
        $this->assertEquals($gameRation->getName(), $player->getEquipments()->first()->getName());
        $this->assertEquals(10, $player->getActionPoint());
    }

    public function testExecuteRation()
    {
        // Standard Ration
        $daedalus = new Daedalus();
        $room = new Place();

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setEquipment($ration)
            ->setHolder($room)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $gameMicrowave = new GameItem();
        $microwave = new ItemConfig();
        $microwave->setName(ToolItemEnum::MICROWAVE);
        $gameMicrowave
            ->setEquipment($microwave)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setHolder($room)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $gameCookedRation = new GameItem();
        $cookedRation = new ItemConfig();
        $cookedRation
             ->setName(GameRationEnum::COOKED_RATION)
        ;
        $gameCookedRation
            ->setEquipment($cookedRation)
            ->setName(GameRationEnum::COOKED_RATION)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
    }
}
