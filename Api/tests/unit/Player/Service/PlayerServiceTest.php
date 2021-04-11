<?php

namespace Mush\Test\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\DeadPlayerInfoRepository;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var PlayerRepository | Mockery\Mock */
    private PlayerRepository $repository;
    /** @var DeadPlayerInfoRepository | Mockery\Mock */
    private DeadPlayerInfoRepository $deadPlayerInfoRepository;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;

    private CharacterConfigCollection $charactersConfig;
    private PlayerService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->repository = Mockery::mock(PlayerRepository::class);
        $this->deadPlayerInfoRepository = Mockery::mock(DeadPlayerInfoRepository::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->charactersConfig = new CharacterConfigCollection();

        $this->service = new PlayerService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->deadPlayerInfoRepository,
            $this->roomLogService,
            $this->statusService,
            $this->gameEquipmentService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreatePlayer()
    {
        $gameConfig = new GameConfig();
        $gameConfig
            ->setInitMovementPoint(0)
            ->setInitActionPoint(1)
            ->setInitSatiety(2)
            ->setInitMoralPoint(3)
            ->setInitHealthPoint(4)
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
        ;

        $user = new User();

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $laboratory = new Place();
        $laboratory->setName(RoomEnum::LABORATORY); // @FIXME: should we move the starting room in the config
        $daedalus->addPlace($laboratory);

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character')
            ->setStatuses(['some status'])
            ->setSkills(['some skills'])
        ;
        $this->charactersConfig->add($characterConfig);
        $this->charactersConfig->add($characterConfig);
        $this->charactersConfig->add($characterConfig);

        $gameConfig
            ->setCharactersConfig($this->charactersConfig)
        ;

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->once()
        ;

        $this->statusService
            ->shouldReceive('createSporeStatus')
            ->once()
        ;

        $player = $this->service->createPlayer($daedalus, $user, 'character');

        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals('character', $player->getCharacterConfig()->getName());
        $this->assertEquals($gameConfig->getInitActionPoint(), $player->getActionPoint());
        $this->assertEquals($gameConfig->getInitMovementPoint(), $player->getMovementPoint());
        $this->assertEquals($gameConfig->getInitHealthPoint(), $player->getHealthPoint());
        $this->assertEquals($gameConfig->getInitMoralPoint(), $player->getMoralPoint());
        $this->assertEquals($gameConfig->getInitSatiety(), $player->getSatiety());
        $this->assertCount(0, $player->getItems());
        $this->assertCount(0, $player->getSkills());
    }

    public function testPlayerDeath()
    {
        $room = new Place();
        $room->setType(PlaceTypeEnum::ROOM)->setName('randomRoom');
        $greatBeyond = new Place();
        $greatBeyond->setName(RoomEnum::GREAT_BEYOND);

        $gameItem = new GameItem();

        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(3)
            ->setDay(5)
            ->addPlace($greatBeyond)
            ->addPlace($room)
        ;

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->addItem($gameItem)
            ->setPlace($room)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;
        $this->gameEquipmentService
            ->shouldReceive('persist')
            ->once()
        ;

        $reason = 'bled';

        $player = $this->service->playerDeath($player, $reason, new \DateTime());

        $this->assertEquals(GameStatusEnum::FINISHED, $player->getGameStatus());
        $this->assertCount(0, $player->getItems());
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getPlayers());
        $this->assertCount(1, $greatBeyond->getPlayers());
    }

    public function testEndPlayer()
    {
        $user = new User();
        $deadPlayerInfo = new DeadPlayerInfo();
        $player = new Player();
        $player
            ->setUser($user)
        ;
        $message = 'message';

        $this->deadPlayerInfoRepository->shouldReceive('findOneByPlayer')->andReturn($deadPlayerInfo)->once();
        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->eventDispatcher->shouldReceive('dispatch');

        $player = $this->service->endPlayer($player, $message);

        $this->assertEquals(GameStatusEnum::CLOSED, $player->getGameStatus());
        $this->assertNull($user->getCurrentGame());
        $this->assertEquals($deadPlayerInfo->getMessage(), $message);
    }
}
