<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Dispense;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class DispenseActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Dispense(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->randomService,
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
            ->setPlace($room);

        $distillerMachine->setActions(new ArrayCollection([$this->actionEntity]));

        $chargeStatus = new ChargeStatus($gameDistillerMachine);
        $chargeStatus
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setCharge(1);

        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameDistillerMachine);

        $gameCoffee = new GameItem();
        $coffee = new ItemConfig();
        $coffee
            ->setName(GameDrugEnum::PHUXX);
        $gameCoffee
            ->setEquipment($coffee)
            ->setName(GameDrugEnum::PHUXX);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('getRandomElements')->andReturn([GameDrugEnum::PHUXX])->once();
        $this->gameEquipmentService
            ->shouldReceive('createGameEquipmentFromName')
            ->with(GameDrugEnum::PHUXX, $daedalus)
            ->andReturn($gameCoffee)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('persist');
        $this->statusService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
