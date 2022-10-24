<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Dispense;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;

class DispenseActionTest extends AbstractActionTest
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    /* @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Dispense(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->randomService,
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

    public function testExecute()
    {
        $room = new Place();

        $distillerMachine = new EquipmentConfig();
        $gameDistillerMachine = new GameEquipment();
        $distillerMachine->setName(EquipmentEnum::NARCOTIC_DISTILLER);
        $gameDistillerMachine
            ->setEquipment($distillerMachine)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setHolder($room);

        $distillerMachine->setActions(new ArrayCollection([$this->actionEntity]));

        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameDistillerMachine);

        $gameDrug = new GameItem();
        $drug = new ItemConfig();
        $drug
            ->setName(GameDrugEnum::PHUXX);
        $gameDrug
            ->setEquipment($drug)
            ->setName(GameDrugEnum::PHUXX);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('getRandomElements')->andReturn([GameDrugEnum::PHUXX])->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
