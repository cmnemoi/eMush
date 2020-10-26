<?php

namespace Mush\Test\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use \Mockery;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Repository\PlantRepository;
use Mush\Item\Service\PlantService;
use Mush\Item\Service\GameFruitServiceInterface;
use PHPUnit\Framework\TestCase;

class PlantServiceTest extends TestCase
{
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var PlantRepository | Mockery\Mock */
    private PlantRepository $plantRepository;

    private PlantService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->plantRepository = Mockery::mock(PlantRepository::class);

        $this->service = new PlantService(
            $this->randomService,
            $this->entityManager,
            $this->plantRepository
        );
    }

    public function testInitGameFruit()
    {
        $daedalus = new Daedalus();

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (Plant $plant) => (
                $plant->getName() === GamePlantEnum::BANANA_TREE &&
                $plant->getOxygen() === 1 &&
                $plant->getFruit()->getDaedalus() === $daedalus &&
                $plant->getFruit()->getHealthPoint() === 1 &&
                $plant->getFruit()->getMoralPoint() === 1 &&
                $plant->getFruit()->getActionPoint() === 1 &&
                $plant->getFruit()->getSatiety() === 1
            ))
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $this->service->initFruits($daedalus);

        //Required otherwise considerated as flacky test
        $this->assertTrue(true);
    }

    public function testCreateFruit()
    {
        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->andReturn(1)
            ->once()
        ;

        $fruit = new Fruit();
        $fruit->setName(GameFruitEnum::BANANA);

        $daedalus = new Daedalus();

        $plant = $this->service->createPlant(GameFruitEnum::BOTTINE, $daedalus);
        $fruit = $plant->getFruit();

        $this->assertInstanceOf(Fruit::class, $fruit);
        $this->assertEquals(GameFruitEnum::BOTTINE, $fruit->getName());
        $this->assertEquals($daedalus, $fruit->getDaedalus());
        $this->assertEquals(1, $fruit->getActionPoint());
        $this->assertEquals(1, $fruit->getMoralPoint());
        $this->assertEquals(0, $fruit->getHealthPoint());
        $this->assertEquals(GameFruitEnum::getGamePlant(GameFruitEnum::BOTTINE), $plant->getName());
    }

}
