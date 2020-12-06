<?php

namespace Mush\Test\Item\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Equipment\CycleHandler\PlantCycleHandler;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Items\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Service\GameItemServiceInterface;
use Mush\Equipment\Service\ItemEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class PlantCycleHandlerTest extends TestCase
{
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var ItemEffectServiceInterface | Mockery\Mock */
    private ItemEffectServiceInterface $itemEffectService;
    /** @var GameConfig */
    private GameConfig $gameConfig;

    private PlantCycleHandler $plantCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->itemService = Mockery::mock(GameItemServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->itemEffectService = Mockery::mock(ItemEffectServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->gameConfig = new GameConfig();

        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig);

        $this->plantCycleHandler = new PlantCycleHandler(
            $this->itemService,
            $this->randomService,
            $this->roomLogService,
            $gameConfigService,
            $this->statusService,
            $this->itemEffectService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNewCycle()
    {
        $plant = new Item();

        $plantType = new Plant();
        $plant->setTypes(new ArrayCollection([$plantType]));

        $this->roomLogService->shouldReceive('createItemLog');
        $this->itemService->shouldReceive('persist')->twice();
        $this->randomService->shouldReceive('randomPercent')->andReturn(100, 1)->twice(); //Plant should not get disease

        $chargeStatus = new ChargeStatus();
        $chargeStatus->setName(ItemStatusEnum::PLANT_YOUNG);
        $chargeStatus->setCharge(1);

        $daedalus = new Daedalus();
        $gamePlant = new GameItem();
        $gamePlant
            ->addStatus($chargeStatus)
            ->setItem($plant)
        ;
        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;
        $this->itemEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

        $this->assertFalse(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => ItemStatusEnum::PLANT_YOUNG === $status->getName())
                ->isEmpty()
        );
        $this->assertTrue(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => ItemStatusEnum::PLANT_DISEASED === $status->getName())
                ->isEmpty()
        );

        $chargeStatus->setCharge(10);

        $gamePlant
            ->setItem($plant)
            ->setRoom(new Room())
        ;

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

        $this->assertTrue(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => ItemStatusEnum::PLANT_YOUNG === $status->getName())
                ->isEmpty()
        );
        $this->assertFalse(
            $gamePlant
                ->getStatuses()
                ->filter(fn (Status $status) => ItemStatusEnum::PLANT_DISEASED === $status->getName())
                ->isEmpty()
        );
    }

    public function testNewDay()
    {
        $this->gameConfig->setMaxItemInInventory(1);

        $daedalus = new Daedalus();
        $daedalus->setOxygen(10);
        $player = new Player();
        $player->setDaedalus($daedalus);
        $room = new Room();
        $room->addPlayer($player);
        $room->setDaedalus($daedalus);

        $newFruit = new Item();
        $newFruit->setName('fruit name');
        $this->itemService->shouldReceive('persist');
        $this->roomLogService->shouldReceive('createItemLog');
        $this->itemService->shouldReceive('createGameItemFromName')->andReturn(new GameItem());
        $this->itemService->shouldReceive('createGameItem')->andReturn(new GameItem());

        $plant = new Item();
        $plant
            ->setName('plant name')
        ;
        $plantType = new Plant();
        $plantType->setFruit($newFruit);

        $plant->setTypes(new ArrayCollection([$plantType]));

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;
        $this->itemEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $chargeStatus = new ChargeStatus();
        $chargeStatus->setName(StatusEnum::CHARGE);
        $chargeStatus->setCharge(1);

        $gamePlant = new GameItem();
        $gamePlant
            ->addStatus($chargeStatus)
            ->setItem($plant)
            ->setRoom($room)
        ;

        $status = new Status();
        $status->setName(ItemStatusEnum::PLANT_THIRSTY);
        $this->statusService
            ->shouldReceive('createCoreItemStatus')
            ->with(ItemStatusEnum::PLANT_THIRSTY, $gamePlant)
            ->andReturn($status)
            ->once()
        ;

        //Mature Plant, no problem
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(2, $room->getItems());
        $this->assertEquals(20, $daedalus->getOxygen());

        $dried = new Status();
        $dried->setName(ItemStatusEnum::PLANT_DRIED_OUT);
        $this->statusService
            ->shouldReceive('createCoreItemStatus')
            ->with(ItemStatusEnum::PLANT_DRIED_OUT, $gamePlant)->andReturn($dried)
            ->once()
        ;

        //Thirsty plant
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(2, $room->getItems());
        $this->assertEquals(30, $daedalus->getOxygen());

        $this->itemService->shouldReceive('createItem')->andReturn(new GameItem());
        $this->itemService->shouldReceive('delete');

        //Dried out plant
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertCount(2, $room->getItems());
        $this->assertNotContains($plant, $room->getItems());
        $this->assertEquals(30, $daedalus->getOxygen());
    }
}
