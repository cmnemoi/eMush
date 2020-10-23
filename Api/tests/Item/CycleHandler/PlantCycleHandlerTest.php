<?php

namespace Mush\Test\Item\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\CycleHandler\PlantCycleHandler;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Service\FruitServiceInterface;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use PHPUnit\Framework\TestCase;
use \Mockery;

class PlantCycleHandlerTest extends TestCase
{
    /** @var ItemServiceInterface | Mockery\Mock */
    private ItemServiceInterface $itemService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var FruitServiceInterface | Mockery\Mock */
    private FruitServiceInterface $fruitService;
    /** @var GameConfig */
    private GameConfig $gameConfig;

    private PlantCycleHandler $plantCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->itemService = Mockery::mock(ItemServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->fruitService = Mockery::mock(FruitServiceInterface::class);
        $this->gameConfig = new GameConfig();

        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig);


        $this->plantCycleHandler = new PlantCycleHandler(
            $this->itemService,
            $this->randomService,
            $this->fruitService,
            $gameConfigService
        );
    }

    public function testNewCycle()
    {
        $gamePlant = new GamePlant();
        $gamePlant->setMaturationTime(10);

        $this->itemService->shouldReceive('persist');
        $this->randomService->shouldReceive('random')->andReturn(100)->once(); //Plant should not get disease

        $plant = new Plant();
        $plant
            ->setLoad(1)
            ->addStatus(PlantStatusEnum::YOUNG)
            ->setGamePlant($gamePlant)
        ;

        $this->plantCycleHandler->handleNewCycle($plant, new \DateTime());

        $this->assertEquals(2, $plant->getLoad());
        $this->assertFalse($plant->isMature());
        $this->assertContains(PlantStatusEnum::YOUNG, $plant->getStatuses());
        $this->assertNotContains(PlantStatusEnum::DISEASED, $plant->getStatuses());

        $plant
            ->setLoad(9)
            ->setGamePlant($gamePlant)
        ;
        $this->randomService->shouldReceive('random')->andReturn(1)->once(); //Plant should get disease

        $this->plantCycleHandler->handleNewCycle($plant, new \DateTime());

        $this->assertEquals(10, $plant->getLoad());
        $this->assertTrue($plant->isMature());
        $this->assertNotContains(PlantStatusEnum::YOUNG, $plant->getStatuses());
        $this->assertContains(PlantStatusEnum::DISEASED, $plant->getStatuses());
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
        $this->fruitService->shouldReceive('createFruit')->andReturn($newFruit);
        $this->itemService->shouldReceive('persist');

        $gameFruit = new GameFruit();
        $gamePlant = new GamePlant();
        $gamePlant
            ->setGameFruit($gameFruit)
            ->setMaturationTime(10)
            ->setOxygen(10)
        ;

        $plant = new Plant();
        $plant
            ->setLoad(10)
            ->setStatuses([])
            ->setGamePlant($gamePlant)
            ->setRoom($room)
        ;

        //Mature Plant, no problem
        $this->plantCycleHandler->handleNewDay($plant, new \DateTime());

        $this->assertContains(PlantStatusEnum::THIRSTY, $plant->getStatuses());
        $this->assertCount(2, $room->getItems());
        $this->assertEquals(20, $daedalus->getOxygen());

        //Thirsty plant
        $this->plantCycleHandler->handleNewDay($plant, new \DateTime());

        $this->assertContains(PlantStatusEnum::DRIED, $plant->getStatuses());
        $this->assertCount(2, $room->getItems());
        $this->assertEquals(30, $daedalus->getOxygen());

        $this->itemService->shouldReceive('createItem')->andReturn(new Item());
        $this->itemService->shouldReceive('delete');

        //Dried out plant
        $this->plantCycleHandler->handleNewDay($plant, new \DateTime());

        $this->assertCount(2, $room->getItems());
        $this->assertNotContains($plant, $room->getItems());
        $this->assertEquals(30, $daedalus->getOxygen());
    }

}