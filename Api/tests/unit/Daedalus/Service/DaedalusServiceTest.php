<?php

namespace Mush\Tests\unit\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\RandomItemPlaces;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\DaedalusInfoRepository;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Repository\TitlePriorityRepositoryInterface;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Daedalus\Service\FunFactsServiceInterface;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Entity\TitleConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Repository\LocalizationConfigRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DaedalusServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var DaedalusRepository|Mockery\Mock */
    private DaedalusRepository $repository;

    /** @var CycleServiceInterface|Mockery\Mock */
    private CycleServiceInterface $cycleService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var LocalizationConfigRepository|Mockery\Mock */
    private LocalizationConfigRepository $localizationConfigRepository;

    /** @var DaedalusInfoRepository|Mockery\Mock */
    private DaedalusInfoRepository $daedalusInfoRepository;

    /** @var DaedalusRepository|Mockery\Mock */
    private DaedalusRepository $daedalusRepository;

    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /** @var Mockery\Mock|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    /** @var FunFactsServiceInterface|Mockery\Mock */
    private FunFactsServiceInterface $funFactsService;

    private DaedalusService $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->repository = \Mockery::mock(DaedalusRepository::class);
        $this->cycleService = \Mockery::mock(CycleServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->localizationConfigRepository = \Mockery::mock(LocalizationConfigRepository::class);
        $this->daedalusInfoRepository = \Mockery::mock(DaedalusInfoRepository::class);
        $this->daedalusRepository = \Mockery::mock(DaedalusRepository::class);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->funFactsService = \Mockery::mock(FunFactsServiceInterface::class);

        $this->service = new DaedalusService(
            $this->entityManager,
            $this->eventService,
            $this->repository,
            $this->cycleService,
            $this->randomService,
            $this->localizationConfigRepository,
            $this->daedalusInfoRepository,
            $this->daedalusRepository,
            self::createStub(TitlePriorityRepositoryInterface::class),
            $this->playerService,
            $this->statusService,
            $this->funFactsService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testCreateDaedalus()
    {
        $roomConfig = new PlaceConfig();

        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();

        $item = new ItemConfig();
        $item->setEquipmentName('item');

        $randomItem = new RandomItemPlaces();
        $randomItem
            ->setItems([$item->getEquipmentName()])
            ->setPlaces([RoomEnum::LABORATORY]);

        $daedalusConfig
            ->setInitShield(1)
            ->setInitFuel(2)
            ->setInitOxygen(3)
            ->setInitHull(4)
            ->setDailySporeNb(4)
            ->setPlaceConfigs(new ArrayCollection([$roomConfig]))
            ->setRandomItemPlaces($randomItem);
        $gameConfig
            ->setDaedalusConfig($daedalusConfig)
            ->setEquipmentsConfig(new ArrayCollection([$item]));

        $this->localizationConfigRepository
            ->shouldReceive('findByLanguage')
            ->with(LanguageEnum::FRENCH)
            ->once()
            ->andReturn(new LocalizationConfig());
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                static fn (DaedalusInitEvent $event) => (
                    $event->getDaedalusConfig() === $daedalusConfig
                )
            )
            ->once();
        $this->entityManager->shouldReceive('beginTransaction')->once();
        $this->entityManager
            ->shouldReceive('persist')
            ->once();
        $this->entityManager
            ->shouldReceive('flush')
            ->once();
        $this->entityManager->shouldReceive('commit')->once();

        $daedalus = $this->service->createDaedalus($gameConfig, 'name', LanguageEnum::FRENCH);

        self::assertInstanceOf(Daedalus::class, $daedalus);
        self::assertSame($daedalusConfig->getInitFuel(), $daedalus->getFuel());
        self::assertSame($daedalusConfig->getInitOxygen(), $daedalus->getOxygen());
        self::assertSame($daedalusConfig->getInitHull(), $daedalus->getHull());
        self::assertSame($daedalusConfig->getInitShield(), $daedalus->getShield());
        self::assertSame(0, $daedalus->getCycle());
        self::assertSame(GameStatusEnum::STANDBY, $daedalus->getGameStatus());
        self::assertNull($daedalus->getCycleStartedAt());
        self::assertSame('name', $daedalus->getDaedalusInfo()->getName());
    }

    public function testStartDaedalus()
    {
        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setCyclePerGameDay(8)->setCycleLength(3 * 60);
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->cycleService
            ->shouldReceive('getInDayCycleFromDate')
            ->andReturn(2)
            ->once();
        $this->cycleService
            ->shouldReceive('getDaedalusStartingCycleDate')
            ->andReturn(new \DateTime('today midnight'))
            ->once();
        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $daedalus = $this->service->startDaedalus($daedalus);

        self::assertSame(GameStatusEnum::STARTING, $daedalus->getGameStatus());
        self::assertEquals(new \DateTime('today midnight'), $daedalus->getCycleStartedAt());
        self::assertSame(2, $daedalus->getCycle());
    }

    public function testFindAvailableCharacterForDaedalus()
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $andieConfig = $daedalus->getGameConfig()->getCharactersConfig()->getByNameOrThrow(CharacterEnum::ANDIE);

        $daedalus->setAvailableCharacters(
            new CharacterConfigCollection([$andieConfig])
        );

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        self::assertCount(1, $result);
        self::assertSame($andieConfig, $result->first());

        $andie = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        $result = $this->service->findAvailableCharacterForDaedalus($daedalus);

        self::assertCount(0, $result);
    }

    public function testGetRandomAsphyxia()
    {
        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $room1 = new Place();
        $room2 = new Place();
        $room3 = new Place();

        $noCapsulePlayer = $this->createPlayer($daedalus, 'noCapsule');
        $twoCapsulePlayer = $this->createPlayer($daedalus, 'twoCapsule');
        $threeCapsulePlayer = $this->createPlayer($daedalus, 'threeCapsule');

        $noCapsulePlayer->setPlace($room1);
        $twoCapsulePlayer->setPlace($room2);
        $threeCapsulePlayer->setPlace($room3);

        $oxCapsuleConfig = new ItemConfig();
        $oxCapsuleConfig->setEquipmentName(ItemEnum::OXYGEN_CAPSULE);

        $oxCapsule1 = new GameItem($twoCapsulePlayer);
        $oxCapsule2 = new GameItem($twoCapsulePlayer);
        $oxCapsule3 = new GameItem($threeCapsulePlayer);
        $oxCapsule4 = new GameItem($threeCapsulePlayer);
        $oxCapsule5 = new GameItem($threeCapsulePlayer);

        $oxCapsule1
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE);
        $oxCapsule2
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE);
        $oxCapsule3
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE);
        $oxCapsule4
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE);
        $oxCapsule5
            ->setEquipment($oxCapsuleConfig)
            ->setName(ItemEnum::OXYGEN_CAPSULE);

        // one player with no capsule
        $this->randomService->shouldReceive('getRandomPlayer')
            ->andReturn($noCapsulePlayer)
            ->once();
        $this->playerService->shouldReceive('killPlayer')->once();

        $result = $this->service->getRandomAsphyxia($daedalus, new \DateTime());

        self::assertCount(2, $twoCapsulePlayer->getEquipments());
        self::assertCount(3, $threeCapsulePlayer->getEquipments());

        // 2 players with capsules
        $this->randomService->shouldReceive('getRandomPlayer')
            ->andReturn($twoCapsulePlayer)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->service->getRandomAsphyxia($daedalus, new \DateTime());

        self::assertCount(2, $twoCapsulePlayer->getEquipments());
        self::assertCount(3, $threeCapsulePlayer->getEquipments());
    }

    public function testSelectAlphaMush()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $gameConfig = $daedalus->getGameConfig();

        $daedalus->setAvailableCharacters($gameConfig->getCharactersConfig()->getAllExceptAndrek());

        $this->randomService->shouldReceive('getRandomElementsFromProbaCollection')
            ->andReturn([CharacterEnum::CHAO, CharacterEnum::ELEESHA])
            ->once();

        $result = $this->service->selectAlphaMush($daedalus, new \DateTime());
    }

    public function testChangeHull()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxHull(100)->setInitHull(10);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $time = new \DateTime('yesterday');

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(static fn (DaedalusEvent $daedalusEvent, $eventName) => ($daedalusEvent->getTime() === $time && $eventName === DaedalusEvent::FINISH_DAEDALUS))
            ->once();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->service->changeVariable(DaedalusVariableEnum::HULL, $daedalus, -20, $time);

        self::assertSame(0, $daedalus->getHull());

        $daedalusConfig->setMaxHull(20);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $this->service->changeVariable(DaedalusVariableEnum::HULL, $daedalus, 100, $time);

        self::assertSame(20, $daedalus->getHull());
    }

    public function testAttributeTitlesGivesTitle()
    {
        $daedalus = new Daedalus();

        $this->setupTestAttributeTitles($daedalus);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->attributeTitles($daedalus, new \DateTime());
    }

    public function testAttributeTitlesDoesNotReassignTitle()
    {
        $daedalus = new Daedalus();

        $players = $this->setupTestAttributeTitles($daedalus);
        $players->first()->addTitle('title');

        $this->eventService->shouldReceive('callEvent')->never();

        $this->service->attributeTitles($daedalus, new \DateTime());
    }

    public function testAttributeTitlesChangedTitleAttributionWhenIncorrect()
    {
        $daedalus = new Daedalus();

        $players = $this->setupTestAttributeTitles($daedalus);
        $players->last()->addTitle('title');

        $this->eventService->shouldReceive('callEvent')->twice();

        $this->service->attributeTitles($daedalus, new \DateTime());
    }

    protected function setupTestAttributeTitles(Daedalus $daedalus): PlayerCollection
    {
        $players = new PlayerCollection();

        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();

        $titleConfigCollection = new ArrayCollection();
        $titleConfig = new TitleConfig();
        $titleConfig->setName('title')->setPriority(['player1', 'player2']);
        $titleConfigCollection->add($titleConfig);

        $gameConfig->setDaedalusConfig($daedalusConfig);
        $gameConfig->setTitleConfigs($titleConfigCollection);

        $daedalus->addTitlePriority(new TitlePriority($titleConfig, $daedalus));

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player1 = $this->createPlayer($daedalus, 'player1');
        $player2 = $this->createPlayer($daedalus, 'player2');
        $players->add($player1);
        $players->add($player2);

        return $players;
    }

    protected function createPlayer(Daedalus $daedalus, string $name): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName($name)->setInitStatuses(new ArrayCollection([]));

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus);

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
