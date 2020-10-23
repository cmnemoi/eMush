<?php

namespace Mush\Test\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CharacterConfigServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepository;
use Mush\Player\Service\PlayerService;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use \Mockery;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PlayerServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var PlayerRepository | Mockery\Mock */
    private PlayerRepository $repository;
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
        $this->tokenStorage = Mockery::mock(TokenStorageInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $characterConfigsService = Mockery::mock(CharacterConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $this->charactersConfig = new CharacterConfigCollection();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();
        $characterConfigsService->shouldReceive('getConfigs')->andReturn($this->charactersConfig)->once();

        $this->service = new PlayerService(
            $this->entityManager,
            $this->eventDispatcher,
            $this->repository,
            $gameConfigService,
            $characterConfigsService,
            $this->tokenStorage
        );
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
            ->twice()
        ;
        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->twice()
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
