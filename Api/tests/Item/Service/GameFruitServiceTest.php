<?php

namespace Mush\Test\Item\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use \Mockery;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Repository\GameFruitRepository;
use Mush\Item\Repository\GamePlantRepository;
use Mush\Item\Service\GameFruitService;
use PHPUnit\Framework\TestCase;

class GameFruitServiceTest extends TestCase
{
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameFruitRepository | Mockery\Mock */
    private GameFruitRepository $gameFruitRepository;
    private GamePlantRepository $gamePlantRepository;
    private GameFruitService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->gamePlantRepository = Mockery::mock(GamePlantRepository::class);
        $this->gameFruitRepository = Mockery::mock(GameFruitRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new GameFruitService(
            $this->randomService,
            $this->entityManager,
            $this->gameFruitRepository,
            $this->gamePlantRepository
        );
    }

    public function testInitGameFruit()
    {
        $daedalus = new Daedalus();

        $this->entityManager
            ->shouldReceive('persist')
            ->withArgs(fn (GameFruit $gameFruit) => (
                $gameFruit->getName() === GameFruitEnum::BANANA &&
                $gameFruit->getDaedalus() === $daedalus &&
                $gameFruit->getHealthPoint() === 1 &&
                $gameFruit->getMoralPoint() === 1 &&
                $gameFruit->getActionPoint() === 1 &&
                $gameFruit->getSatiety() === 1 &&
                $gameFruit->getGamePlant()->getName() === GamePlantEnum::BANANA_TREE &&
                $gameFruit->getGamePlant()->getMaturationTime() === 36 &&
                $gameFruit->getGamePlant()->getOxygen() === 1
            ))
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $this->service->initGameFruits($daedalus);

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

        $fruit = new GameFruit();
        $fruit->setName(GameFruitEnum::BANANA);

        $this->gameFruitRepository
            ->shouldReceive('findBy')
            ->andReturn([$fruit])
            ->once()
        ;

        $daedalus = new Daedalus();

        $gameFruit = $this->service->createFruit(GameFruitEnum::BOTTINE, $daedalus);

        $this->assertInstanceOf(GameFruit::class, $gameFruit);
        $this->assertEquals(GameFruitEnum::BOTTINE, $gameFruit->getName());
        $this->assertEquals($daedalus, $gameFruit->getDaedalus());
        $this->assertEquals(1, $gameFruit->getActionPoint());
        $this->assertEquals(1, $gameFruit->getMoralPoint());
        $this->assertEquals(0, $gameFruit->getHealthPoint());
        $this->assertEquals(GameFruitEnum::getGamePlant(GameFruitEnum::BOTTINE), $gameFruit->getGamePlant()->getName());
        $this->assertEquals(1, $gameFruit->getGamePlant()->getOxygen());
        $this->assertEquals(1, $gameFruit->getGamePlant()->getMaturationTime());
    }
}
