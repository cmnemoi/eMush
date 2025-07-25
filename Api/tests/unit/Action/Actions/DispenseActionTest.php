<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Dispense;
use Mush\Action\Entity\ActionResult\Success;
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

/**
 * @internal
 */
final class DispenseActionTest extends AbstractActionTest
{
    private Mockery\Mock|RandomServiceInterface $randomService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->createActionEntity(ActionEnum::BUILD);

        $this->actionHandler = new Dispense(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
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

    public function testExecute()
    {
        $room = new Place();

        $distillerMachine = new EquipmentConfig();
        $gameDistillerMachine = new GameEquipment($room);
        $distillerMachine->setEquipmentName(EquipmentEnum::NARCOTIC_DISTILLER);
        $gameDistillerMachine
            ->setEquipment($distillerMachine)
            ->setName(EquipmentEnum::COFFEE_MACHINE);

        $distillerMachine->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameDistillerMachine);

        $gameDrug = new GameItem(new Place());
        $drug = new ItemConfig();
        $drug
            ->setEquipmentName(GameDrugEnum::PHUXX);
        $gameDrug
            ->setEquipment($drug)
            ->setName(GameDrugEnum::PHUXX);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('getRandomElements')->andReturn([GameDrugEnum::PHUXX])->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertCount(0, $player->getStatuses());
        self::assertSame(10, $player->getActionPoint());
    }
}
