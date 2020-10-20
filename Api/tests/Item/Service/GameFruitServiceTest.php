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
use Mush\Item\Service\GameFruitService;
use PHPUnit\Framework\TestCase;

class GameFruitServiceTest extends TestCase
{
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameFruitRepository | Mockery\Mock */
    private GameFruitRepository $repository;
    private GameFruitService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(GameFruitRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new GameFruitService(
            $this->randomService,
            $this->entityManager,
            $this->repository
        );
    }

    public function testCreateBanana()
    {
        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $daedalus = new Daedalus();

        $banana = $this->service->createBanana($daedalus);

        $this->assertInstanceOf(GameFruit::class, $banana);
        $this->assertEquals(GameFruitEnum::BANANA, $banana->getName());
        $this->assertEquals($daedalus, $banana->getDaedalus());
        $this->assertEquals(1, $banana->getActionPoint());
        $this->assertEquals(1, $banana->getMoralPoint());
        $this->assertEquals(1, $banana->getHealthPoint());
        $this->assertEquals(GamePlantEnum::BANANA_TREE, $banana->getGamePlant()->getName());
        $this->assertEquals(1, $banana->getGamePlant()->getOxygen());
        $this->assertEquals(36, $banana->getGamePlant()->getMaturationTime());
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

        $this->repository
            ->shouldReceive('findBy')
            ->andReturn([$fruit])
            ->once()
        ;

        $daedalus = new Daedalus();

        $gameFruit = $this->service->createFruit($daedalus);

        $this->assertInstanceOf(GameFruit::class, $gameFruit);
        $this->assertEquals(GameFruitEnum::getAll()[1], $gameFruit->getName());
        $this->assertEquals($daedalus, $gameFruit->getDaedalus());
        $this->assertEquals(1, $gameFruit->getActionPoint());
        $this->assertEquals(1, $gameFruit->getMoralPoint());
        $this->assertEquals(0, $gameFruit->getHealthPoint());
        $this->assertEquals(GamePlantEnum::getAll()[1], $gameFruit->getGamePlant()->getName());
        $this->assertEquals(1, $gameFruit->getGamePlant()->getOxygen());
        $this->assertEquals(1, $gameFruit->getGamePlant()->getMaturationTime());
    }

}