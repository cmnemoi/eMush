<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Cook;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class CookActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::COOK, 1);

        $this->action = new Cook(
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
        // frozen fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem();
        $ration = new EquipmentConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setHolder($player)
            ->setName('ration')
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::FROZEN);
        $frozenStatus = new Status($gameRation, $statusConfig);

        $gameKitchen = new GameEquipment();
        $kitchen = new ItemConfig();
        $kitchen->setName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN)
            ->setHolder($room)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
        ;
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getEquipments());
        $this->assertEquals($gameRation->getName(), $player->getEquipments()->first()->getName());
        $this->assertCount(0, $player->getStatuses());

        $room = new Place();
    }

    public function testExecuteRation()
    {
        $room = new Place();

        // Standard Ration
        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setEquipment($ration)
            ->setHolder($room)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $gameKitchen = new GameEquipment();
        $kitchen = new EquipmentConfig();
        $kitchen->setName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN)
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
        $this->eventService->shouldReceive('callEvent')->once();
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
    }
}
