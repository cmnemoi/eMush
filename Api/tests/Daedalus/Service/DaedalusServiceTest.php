<?php

namespace Mush\Test\Daedalus\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusConfigServiceInterface;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Service\CycleServiceInterface;
use \Mockery;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Service\GameFruitServiceInterface;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Enum\RoomEnum;
use Mush\Room\Service\RoomServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var DaedalusRepository | Mockery\Mock */
    private DaedalusRepository $repository;
    /** @var RoomServiceInterface | Mockery\Mock */
    private RoomServiceInterface $roomService;
    /** @var CycleServiceInterface | Mockery\Mock */
    private CycleServiceInterface $cycleService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var GameFruitServiceInterface | Mockery\Mock */
    private GameFruitServiceInterface $gameFruitService;
    private DaedalusConfig $daedalusConfig;
    private DaedalusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->repository = Mockery::mock(DaedalusRepository::class);
        $this->roomService = Mockery::mock(RoomServiceInterface::class);
        $this->cycleService = Mockery::mock(CycleServiceInterface::class);
        $this->itemService = Mockery::mock(GameItemServiceInterface::class);
        $this->gameFruitService = Mockery::mock(GameFruitServiceInterface::class);
        $daedalusConfig = Mockery::mock(DaedalusConfigServiceInterface::class);
        $this->daedalusConfig = new DaedalusConfig();
        $daedalusConfig->shouldReceive('getConfig')->andReturn($this->daedalusConfig)->once();

        $this->service = new DaedalusService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->roomService,
            $this->cycleService,
            $this->itemService,
            $this->gameFruitService,
            $daedalusConfig
        );
    }

    public function testCreateDaedalus()
    {
        $roomConfig = new RoomConfig();

        $this->daedalusConfig
            ->setInitShield(1)
            ->setInitFuel(2)
            ->setInitOxygen(3)
            ->setInitHull(4)
            ->setRooms([$roomConfig])
        ;

        $room = new Room();
        $room->setName(RoomEnum::LABORATORY);
        $this->roomService
            ->shouldReceive('createRoom')
            ->andReturn($room)
            ->once()
        ;

        $this->cycleService
            ->shouldReceive('getCycleFromDate')
            ->andReturn(5)
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->twice()
        ;


        $this->itemService
            ->shouldReceive('persist')
            ->once()
        ;
        $banana = new GameFruit();
        $bananaTree = new GamePlant();
        $bananaTree
            ->setName(GamePlantEnum::BANANA_TREE)
        ;
        $banana
            ->setGamePlant($bananaTree)
            ->setName(GameFruitEnum::BANANA)
        ;

        $this->gameFruitService
            ->shouldReceive('initGameFruits')
            ->andReturn($banana)
            ->once()
        ;


        $daedalus = $this->service->createDaedalus();

        $this->assertInstanceOf(Daedalus::class, $daedalus);
        $this->assertEquals($this->daedalusConfig->getInitFuel(), $daedalus->getFuel());
        $this->assertEquals($this->daedalusConfig->getInitOxygen(), $daedalus->getOxygen());
        $this->assertEquals($this->daedalusConfig->getInitHull(), $daedalus->getHull());
        $this->assertEquals($this->daedalusConfig->getInitShield(), $daedalus->getShield());
        $this->assertEquals(5, $daedalus->getCycle());
        $this->assertCount(1, $daedalus->getRooms());
        $this->assertCount(0, $daedalus->getPlayers());
    }
}
