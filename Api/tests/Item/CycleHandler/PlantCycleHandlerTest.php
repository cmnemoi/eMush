<?php

namespace Mush\Test\Item\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\CycleHandler\PlantCycleHandler;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\PlantEffect;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Service\ItemEffectServiceInterface;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;

class PlantCycleHandlerTest extends TestCase
{
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
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
        $this->gameConfig = new GameConfig();

        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig);

        $this->plantCycleHandler = new PlantCycleHandler(
            $this->itemService,
            $this->randomService,
            $this->roomLogService,
            $gameConfigService,
            $this->itemEffectService
        );
    }

    public function testNewCycle()
    {
        $plant = new Plant();

        $this->roomLogService->shouldReceive('createItemLog');
        $this->itemService->shouldReceive('persist');
        $this->randomService->shouldReceive('random')->andReturn(100)->once(); //Plant should not get disease

        $daedalus = new Daedalus();
        $gamePlant = new GameItem();
        $gamePlant
            ->setCharge(1)
            ->addStatus(PlantStatusEnum::YOUNG)
            ->setItem($plant)
        ;
        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;
        $this->itemEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $this->plantCycleHandler->handleNewCycle($gamePlant, $daedalus, new \DateTime());

        $this->assertEquals(2, $gamePlant->getCharge());
        $this->assertContains(PlantStatusEnum::YOUNG, $gamePlant->getStatuses());
        $this->assertNotContains(PlantStatusEnum::DISEASED, $gamePlant->getStatuses());

        $gamePlant
            ->setCharge(9)
            ->setItem($plant)
            ->setRoom(new Room())
        ;
        $this->randomService->shouldReceive('random')->andReturn(1)->once(); //Plant should get disease

        $this->plantCycleHandler->handleNewCycle($gamePlant,$daedalus, new \DateTime());

        $this->assertEquals(10, $gamePlant->getCharge());
        $this->assertNotContains(PlantStatusEnum::YOUNG, $gamePlant->getStatuses());
        $this->assertContains(PlantStatusEnum::DISEASED, $gamePlant->getStatuses());
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

        $newFruit  = new Fruit();
        $newFruit->setName('fruit name');
        $this->itemService->shouldReceive('persist');
        $this->roomLogService->shouldReceive('createItemLog');
        $this->itemService->shouldReceive('createGameItemFromName')->andReturn(new GameItem());

        $plant = new Plant();
        $plant
            ->setName('plant name')
            ->setFruit($newFruit)
        ;
        $plantEffect = new PlantEffect();
        $plantEffect
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;
        $this->itemEffectService->shouldReceive('getPlantEffect')->andReturn($plantEffect);

        $gamePlant = new GameItem();
        $gamePlant
            ->setCharge(10)
            ->setStatuses([])
            ->setItem($plant)
            ->setRoom($room)
        ;

        //Mature Plant, no problem
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertContains(PlantStatusEnum::THIRSTY, $gamePlant->getStatuses());
        $this->assertCount(2, $room->getItems());
        $this->assertEquals(20, $daedalus->getOxygen());

        //Thirsty plant
        $this->plantCycleHandler->handleNewDay($gamePlant, $daedalus, new \DateTime());

        $this->assertContains(PlantStatusEnum::DRIED, $gamePlant->getStatuses());
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
