<?php

namespace Mush\Tests\unit\Player\Service;

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
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\DeadPlayerInfoRepository;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var Mockery\Mock|PlayerRepository */
    private PlayerRepository $repository;

    /** @var Mockery\Mock|RoomLogServiceInterface */
    private RoomLogServiceInterface $roomLogService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private GameEquipmentServiceInterface $gameEquipmentService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var Mockery\Mock|PlayerInfoRepositoryInterface */
    private PlayerInfoRepositoryInterface $playerInfoRepository;

    private CharacterConfigCollection $charactersConfigs;
    private PlayerService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->repository = \Mockery::mock(PlayerRepository::class);
        $this->deadPlayerInfoRepository = \Mockery::mock(DeadPlayerInfoRepository::class);
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->playerInfoRepository = \Mockery::mock(PlayerInfoRepositoryInterface::class);

        $this->charactersConfigs = new CharacterConfigCollection();

        $this->service = new PlayerService(
            $this->entityManager,
            $this->eventService,
            $this->repository,
            $this->roomLogService,
            $this->gameEquipmentService,
            $this->randomService,
            $this->playerInfoRepository,
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
            ->setInitHealthPoint(4);
        $this->charactersConfigs->add($characterConfig);

        $gameConfig
            ->setCharactersConfig($this->charactersConfigs);

        $this->entityManager
            ->shouldReceive('persist')
            ->times(2);
        $this->entityManager
            ->shouldReceive('flush')
            ->times(2);
        $this->eventService
            ->shouldReceive('callEvent')
            ->once();

        $player = $this->service->createPlayer($daedalus, $user, 'character');

        self::assertInstanceOf(Player::class, $player);
        self::assertSame('character', $player->getPlayerInfo()->getCharacterConfig()->getCharacterName());
        self::assertSame($characterConfig->getInitActionPoint(), $player->getActionPoint());
        self::assertSame($characterConfig->getInitMovementPoint(), $player->getMovementPoint());
        self::assertSame($characterConfig->getInitHealthPoint(), $player->getHealthPoint());
        self::assertSame($characterConfig->getInitMoralPoint(), $player->getMoralPoint());
        self::assertSame($characterConfig->getInitSatiety(), $player->getSatiety());
        self::assertCount(0, $player->getEquipments());
        self::assertCount(0, $player->getSkills());
    }

    public function testPlayerDeath()
    {
        $gameConfig = new GameConfig();
        $room = new Place();
        $room->setType(PlaceTypeEnum::ROOM)->setName('randomRoom');

        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(3)
            ->setDay(5)
            ->addPlace($room);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('name');

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlace($room);

        $gameItem = new GameItem($player);

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        $closedPlayer = $playerInfo->getClosedPlayer();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager
            ->shouldReceive('flush')
            ->once();
        $this->gameEquipmentService
            ->shouldReceive('persist')
            ->once();

        $reason = 'bled';

        $player = $this->service->playerDeath($player, $reason, new \DateTime());

        self::assertSame(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        self::assertCount(0, $player->getEquipments());
        self::assertCount(1, $room->getEquipments());
        self::assertCount(1, $room->getPlayers());
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

        $this->eventService->shouldReceive('callEvent');

        $player = $this->service->endPlayer($player, $message, []);

        self::assertSame(GameStatusEnum::CLOSED, $playerInfo->getGameStatus());
        self::assertSame($closedPlayer->getMessage(), $message);
    }
}
