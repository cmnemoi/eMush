<?php

namespace Mush\Tests\unit\Daedalus\Event;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Listener\DaedalusCycleSubscriber;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Daedalus\Service\DispatchCycleIncidentsService;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\DifficultyServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\Random\FakeRandomFloatService;
use Mush\Game\Service\Random\ProbaCollectionRandomElementService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * @internal
 */
final class DaedalusCycleEventTest extends TestCase
{
    /** @var DaedalusServiceInterface|Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;

    private DispatchCycleIncidentsService $dispatchCycleIncidents;

    /** @var DifficultyServiceInterface|Mockery\Mock */
    private DifficultyServiceInterface $difficultyService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var LockFactory|Mockery\Spy */
    private LockFactory $lockFactory;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private DaedalusCycleSubscriber $daedalusCycleSubscriber;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);
        $this->dispatchCycleIncidents = new DispatchCycleIncidentsService(
            daedalusIncidentService: self::createStub(DaedalusIncidentServiceInterface::class),
            d100Roll: new FakeD100RollService(),
            eventService: self::createStub(EventServiceInterface::class),
            gameEquipmentRepository: self::createStub(GameEquipmentRepositoryInterface::class),
            probaCollectionRandomElement: new ProbaCollectionRandomElementService(new FakeGetRandomIntegerService(0)),
            randomFloat: new FakeRandomFloatService(),
        );
        $this->difficultyService = \Mockery::Mock(DifficultyServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->lockFactory = \Mockery::spy(LockFactory::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $lockInterface = \Mockery::mock(LockInterface::class);
        $lockInterface->shouldReceive('acquire')->andReturn(true);

        $lockInterface->shouldReceive('release');

        $this->lockFactory->shouldReceive('createLock')->andReturn($lockInterface);

        $this->daedalusCycleSubscriber = new DaedalusCycleSubscriber(
            $this->daedalusService,
            $this->dispatchCycleIncidents,
            $this->difficultyService,
            $this->eventService,
            $this->lockFactory,
            $this->randomService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testOnDaedalusDestruction()
    {
        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitHull(0);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalusInfo->setGameConfig($gameConfig);
        $daedalus->setDaedalusVariables($daedalusConfig);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        $mushConfig = new StatusConfig();
        $mushConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mush = new Status($player, $mushConfig);

        $date = new \DateTime('tomorrow');

        $event = new DaedalusCycleEvent($daedalus, [DaedalusEvent::FINISH_DAEDALUS], $date);

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(static fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::FINISH_DAEDALUS))
            ->once();

        $this->daedalusCycleSubscriber->dispatchNewCycleIncidents($event);
    }
}
