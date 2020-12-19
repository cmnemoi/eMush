<?php

namespace Mush\Test\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\Service\PlayerService;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    /** @var TokenStorageInterface | Mockery\Mock */
    private TokenStorageInterface $tokenStorage;
    private GameConfig $gameConfig;
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
        $this->tokenStorage = Mockery::mock(TokenStorageInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $this->charactersConfig = new CharacterConfigCollection();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->service = new PlayerService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $this->roomLogService,
            $this->statusService,
            $gameConfigService,
            $this->tokenStorage
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
        $this->gameConfig
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

        $token = Mockery::mock(AbstractToken::class);
        $token
            ->shouldReceive('getUser')
            ->andReturn($user)
        ;

        $this->tokenStorage
            ->shouldReceive('getToken')
            ->andReturn($token)
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $daedalus = new Daedalus();
        $laboratory = new Room();
        $laboratory->setName(RoomEnum::LABORATORY); // @FIXME: should we move the starting room in the config
        $daedalus->addRoom($laboratory);

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character')
            ->setStatuses(['some status'])
            ->setSkills(['some skills'])
        ;
        $this->charactersConfig->add($characterConfig);
        $this->charactersConfig->add($characterConfig);
        $this->charactersConfig->add($characterConfig);

        $this->gameConfig
            ->setCharactersConfig($this->charactersConfig)
        ;

        $player = $this->service->createPlayer($daedalus, 'character');

        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals('character', $player->getPerson());
        $this->assertEquals($this->gameConfig->getInitActionPoint(), $player->getActionPoint());
        $this->assertEquals($this->gameConfig->getInitMovementPoint(), $player->getMovementPoint());
        $this->assertEquals($this->gameConfig->getInitHealthPoint(), $player->getHealthPoint());
        $this->assertEquals($this->gameConfig->getInitMoralPoint(), $player->getMoralPoint());
        $this->assertEquals($this->gameConfig->getInitSatiety(), $player->getSatiety());
        $this->assertCount(0, $player->getItems());
        $this->assertCount(1, $player->getStatuses());
        $this->assertCount(0, $player->getSkills());
    }
}
