<?php

namespace Mush\Test\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\DeadPlayerInfoRepository;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var PlayerRepository|Mockery\Mock */
    private PlayerRepository $repository;
    /** @var DeadPlayerInfoRepository|Mockery\Mock */
    private DeadPlayerInfoRepository $deadPlayerInfoRepository;
    /** @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;

    private CharacterConfigCollection $charactersConfigs;
    private PlayerService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->repository = \Mockery::mock(PlayerRepository::class);
        $this->deadPlayerInfoRepository = \Mockery::mock(DeadPlayerInfoRepository::class);
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->charactersConfigs = new CharacterConfigCollection();

        $this->service = new PlayerService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->deadPlayerInfoRepository,
            $this->roomLogService,
            $this->gameEquipmentService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreatePlayer()
    {
        $user = new User();
        $gameConfig = new GameConfig();

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $laboratory = new Place();
        $laboratory->setName(RoomEnum::LABORATORY); // @FIXME: should we move the starting room in the config
        $daedalus->addPlace($laboratory);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName('some status');

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setCharacterName('character')
            ->setInitStatuses(new ArrayCollection([$statusConfig]))
            ->setSkills(['some skills'])
            ->setInitMovementPoint(0)
            ->setInitActionPoint(1)
            ->setInitSatiety(2)
            ->setInitMoralPoint(3)
            ->setInitHealthPoint(4)
        ;
        $this->charactersConfigs->add($characterConfig);

        $gameConfig
            ->setCharactersConfig($this->charactersConfigs)
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->times(2)
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->times(2)
        ;
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
        ;

        $player = $this->service->createPlayer($daedalus, $user, 'character');

        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals('character', $player->getPlayerInfo()->getCharacterConfig()->getCharacterName());
        $this->assertEquals($characterConfig->getInitActionPoint(), $player->getActionPoint());
        $this->assertEquals($characterConfig->getInitMovementPoint(), $player->getMovementPoint());
        $this->assertEquals($characterConfig->getInitHealthPoint(), $player->getHealthPoint());
        $this->assertEquals($characterConfig->getInitMoralPoint(), $player->getMoralPoint());
        $this->assertEquals($characterConfig->getInitSatiety(), $player->getSatiety());
        $this->assertCount(0, $player->getEquipments());
        $this->assertCount(0, $player->getSkills());
    }

    public function testPlayerDeath()
    {
        $room = new Place();
        $room->setType(PlaceTypeEnum::ROOM)->setName('randomRoom');

        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(3)
            ->setDay(5)
            ->addPlace($room)
        ;

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('name');

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;

        $gameItem = new GameItem($player);

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        $closedPlayer = $playerInfo->getClosedPlayer();

        $this->entityManager->shouldReceive('persist')->once();
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

        $this->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $this->assertCount(0, $player->getEquipments());
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $room->getPlayers());
    }

    public function testEndPlayer()
    {
        $user = new User();

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $player->setPlayerInfo($playerInfo);

        $closedPlayer = $playerInfo->getClosedPlayer();

        $message = 'message';

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->eventDispatcher->shouldReceive('dispatch');

        $player = $this->service->endPlayer($player, $message);

        $this->assertEquals(GameStatusEnum::CLOSED, $playerInfo->getGameStatus());
        $this->assertEquals($closedPlayer->getMessage(), $message);
    }
}
