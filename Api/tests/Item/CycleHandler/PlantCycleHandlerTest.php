<?php

namespace Mush\Test\Item\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\CycleHandler\FruitCycleHandler;
use Mush\Item\CycleHandler\PlantCycleHandler;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
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
        $this->fruitService = Mockery::mock(FruitServiceInterface::class);
        $this->gameConfig = new GameConfig();

        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig);


        $this->plantCycleHandler = new PlantCycleHandler(
            $this->itemService,
            $this->fruitService,
            $gameConfigService
        );
    }

    public function testNewCycle()
    {
        $gamePlant = new GamePlant();
        $gamePlant->setMaturationTime(10);

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

        $plant
            ->setLoad(9)
            ->setGamePlant($gamePlant)
        ;

        $this->plantCycleHandler->handleNewCycle($plant, new \DateTime());

        $this->assertEquals(10, $plant->getLoad());
        $this->assertTrue($plant->isMature());
        $this->assertNotContains(PlantStatusEnum::YOUNG, $plant->getStatuses());
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

        $this->plantCycleHandler->handleNewDay($plant, new \DateTime());

        $this->assertContains(PlantStatusEnum::THIRSTY, $plant->getStatuses());
        $this->assertCount(2, $room->getItems());
        $this->assertEquals(20, $daedalus->getOxygen());
    }
}