<?php

namespace Mush\Test\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Enum\RoomEnum;
use Mush\Room\Service\RoomServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
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
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
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
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);

        $this->service = new DaedalusService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->roomService,
            $this->cycleService,
            $this->gameEquipmentService,
            $this->randomService,
            $this->roomLogService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateDaedalus()
    {
        $roomConfig = new RoomConfig();

        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();

        $item = new ItemConfig();
        $item->setName('item');

        $randomItem = new RandomItemPlaces();
        $randomItem
            ->setItems([$item->getName()])
            ->setPlaces([RoomEnum::LABORATORY])
        ;

        $daedalusConfig
            ->setInitShield(1)
            ->setInitFuel(2)
            ->setInitOxygen(3)
            ->setInitHull(4)
            ->setDailySporeNb(4)
            ->setRoomConfigs(new ArrayCollection([$roomConfig]))
            ->setRandomItemPlace($randomItem)
        ;
        $gameConfig
            ->setDaedalusConfig($daedalusConfig)
            ->setEquipmentsConfig(new ArrayCollection([$item]))
        ;

        $room = new Room();
        $room->setName(RoomEnum::LABORATORY);
        $this->roomService
            ->shouldReceive('createRoom')
            ->andReturn($room)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->andReturn(0)
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

        $this->gameEquipmentService
            ->shouldReceive('createGameEquipment')
            ->andReturn(new GameItem())
            ->once()
        ;
        $this->gameEquipmentService
            ->shouldReceive('persist')
            ->once()
        ;

        $daedalus = $this->service->createDaedalus($gameConfig);

        $this->assertInstanceOf(Daedalus::class, $daedalus);
        $this->assertEquals($daedalusConfig->getInitFuel(), $daedalus->getFuel());
        $this->assertEquals($daedalusConfig->getInitOxygen(), $daedalus->getOxygen());
        $this->assertEquals($daedalusConfig->getInitHull(), $daedalus->getHull());
        $this->assertEquals($daedalusConfig->getInitShield(), $daedalus->getShield());
        $this->assertEquals(5, $daedalus->getCycle());
        $this->assertCount(1, $daedalus->getRooms());
        $this->assertCount(0, $daedalus->getPlayers());
    }

    public function testFindAvailableCharacterForDaedalus()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();

        $daedalus->setGameConfig($gameConfig);

        $characterConfigCollection = new ArrayCollection();
        $gameConfig->setCharactersConfig($characterConfigCollection);

        $characterConfig = new CharacterConfig();
        $characterConfig->setName('character_1');
        $characterConfigCollection->add($characterConfig);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        $this->assertCount(1, $result);
        $this->assertEquals($characterConfig, $result->first());

        $player = new Player();
        $player->setCharacterConfig($characterConfig);
        $daedalus->addPlayer($player);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        $this->assertCount(0, $result);
    }
}
