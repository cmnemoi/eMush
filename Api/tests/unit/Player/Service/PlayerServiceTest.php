<?php

namespace Mush\Test\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
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
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

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
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->charactersConfig = new CharacterConfigCollection();
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new PlayerService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->roomLogService,
            $this->statusService,
            $this->randomService
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
            ->twice()
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

    public function testEndPlayer()
    {
        $user = new User();
        $deadPlayerInfo = new DeadPlayerInfo();
        $player = new Player();
        $player
            ->setUser($user)
            ->setDeadPlayerInfo($deadPlayerInfo)
        ;
        $message = 'message';

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ]);

        $this->eventDispatcher->shouldReceive('dispatch');

        $player = $this->service->endPlayer($player, $message);

        $this->assertEquals(GameStatusEnum::CLOSED, $player->getGameStatus());
        $this->assertNull($user->getCurrentGame());
    }
}
