<?php

namespace Mush\Test\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use \Mockery;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Repository\GameFruitRepository;
use Mush\Item\Repository\GamePlantRepository;
use Mush\Item\Service\FruitService;
use Mush\Item\Service\GameFruitService;
use Mush\Item\Service\GameFruitServiceInterface;
use Mush\Item\Service\ItemServiceInterface;
use PHPUnit\Framework\TestCase;

class FruitServiceTest extends TestCase
{
    /** @var GameFruitServiceInterface | Mockery\Mock */
    private GameFruitServiceInterface $gameFruitService;
    /** @var ItemServiceInterface | Mockery\Mock */
    private ItemServiceInterface $itemService;

    private FruitService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->itemService = Mockery::mock(ItemServiceInterface::class);
        $this->gameFruitService = Mockery::mock(GameFruitServiceInterface::class);

        $this->itemService->shouldReceive('persist');

        $this->service = new FruitService(
            $this->itemService,
            $this->gameFruitService
        );
    }

    public function testCreatePlant()
    {
        $gamePlant = new GamePlant();
        $gamePlant
            ->setName('name')
            ->setOxygen(1)
            ->setMaturationTime(2)
        ;

        $plant = $this->service->createPlant($gamePlant);

        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertContains(PlantStatusEnum::YOUNG, $plant->getStatuses());
        $this->assertEquals('name', $plant->getName());
        $this->assertEquals($gamePlant, $plant->getGamePlant());
    }

    public function testCreateFruit()
    {
        $gameFruit = new GameFruit();
        $gameFruit
            ->setName('name')
            ->setActionPoint(1)
            ->setHealthPoint(2)
            ->setMoralPoint(3)
        ;

        $fruit = $this->service->createFruit($gameFruit);

        $this->assertInstanceOf(Fruit::class, $fruit);
        $this->assertEquals('name', $fruit->getName());
        $this->assertEquals($gameFruit, $fruit->getGameFruit());
    }

    public function testCreatePlantFromNameNotExisting()
    {
        $daedalus = new Daedalus();

        $this->gameFruitService
            ->shouldReceive('findOneGamePlantByName')
            ->andReturn(null)
        ;

        $gameFruit = new GameFruit();
        $gameFruit->setName(GameFruitEnum::MEZTINE);
        $gamePlant = new GamePlant();
        $gamePlant->setName(GamePlantEnum::CACTAX);
        $gameFruit->setGamePlant($gamePlant);
        $this->gameFruitService
            ->shouldReceive('createFruit')
            ->andReturn($gameFruit)
        ;

        $plant = $this->service->createPlantFromName(GamePlantEnum::CACTAX, $daedalus);

        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertEquals(GamePlantEnum::CACTAX, $plant->getName());
        $this->assertEquals(GameFruitEnum::MEZTINE, $plant->getGamePlant()->getGameFruit()->getName());
    }

    public function testCreatePlantFromNameExisting()
    {
        $daedalus = new Daedalus();

        $gameFruit = new GameFruit();
        $gameFruit->setName(GameFruitEnum::MEZTINE);
        $gamePlant = new GamePlant();
        $gamePlant->setName(GamePlantEnum::CACTAX);
        $gameFruit->setGamePlant($gamePlant);

        $this->gameFruitService
            ->shouldReceive('findOneGamePlantByName')
            ->andReturn($gamePlant)
        ;

        $plant = $this->service->createPlantFromName(GamePlantEnum::CACTAX, $daedalus);

        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertEquals(GamePlantEnum::CACTAX, $plant->getName());
        $this->assertEquals(GameFruitEnum::MEZTINE, $plant->getGamePlant()->getGameFruit()->getName());
    }
}