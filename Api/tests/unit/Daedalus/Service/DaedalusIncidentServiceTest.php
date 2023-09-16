<?php

namespace Mush\Tests\unit\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Service\DaedalusIncidentService;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DaedalusIncidentServiceTest extends TestCase
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var GameEquipmentRepository|Mockery\Mock */
    private GameEquipmentRepository $gameEquipmentRepository;
    /** @var LoggerInterface|Mocker\Mock */
    private LoggerInterface $logger;

    private DaedalusIncidentServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->gameEquipmentRepository = \Mockery::mock(GameEquipmentRepository::class);
        $this->logger = \Mockery::mock(LoggerInterface::class);

        $this->service = new DaedalusIncidentService(
            $this->randomService,
            $this->eventService,
            $this->gameEquipmentRepository,
            $this->logger
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testHandleFireEvents()
    {
        $this->randomService->shouldReceive('poissonRandom')->andReturn(0)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([])->once();

        $fires = $this->service->handleFireEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $fires);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $room1 = new Place();
        $room1->setDaedalus(new Daedalus());

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$room1])
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => (
                $event->getStatusHolder() === $room1
                && in_array(EventEnum::NEW_CYCLE, $event->getTags())
                && $event->getStatusName() === StatusEnum::FIRE
            ))
            ->once()
        ;

        $fires = $this->service->handleFireEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $fires);
    }

    public function testHandleTremorEvents()
    {
        $this->randomService->shouldReceive('poissonRandom')->andReturn(0)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([])->once();

        $fires = $this->service->handleTremorEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $fires);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $room1 = new Place();
        $room1->setDaedalus(new Daedalus());

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$room1])
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (RoomEvent $event) => $event->getPlace() === $room1 && in_array(EventEnum::NEW_CYCLE, $event->getTags()))
            ->once()
        ;

        $fires = $this->service->handleTremorEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $fires);
    }

    public function testHandleElectricArcEvents()
    {
        $this->randomService->shouldReceive('poissonRandom')->andReturn(0)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([])->once();

        $fires = $this->service->handleElectricArcEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $fires);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $room1 = new Place();
        $room1->setDaedalus(new Daedalus());

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$room1])
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (RoomEvent $event) => $event->getPlace() === $room1 && in_array(EventEnum::NEW_CYCLE, $event->getTags()))
            ->once()
        ;

        $fires = $this->service->handleElectricArcEvents(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $fires);
    }

    public function testHandleEquipmentBreakEvents()
    {
        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution(['communication_center' => 1]);

        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);

        $daedalus = new Daedalus();

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->randomService->shouldReceive('poissonRandom')->andReturn(0)->once();

        $broken = $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        $this->assertEquals(0, $broken);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $place = new Place();
        $place->setDaedalus($daedalus);
        $equipment = new GameEquipment($place);

        $this->isFalse($equipment->isBroken());

        $this->gameEquipmentRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->withArgs(['communication_center', $daedalus])
            ->andReturn([$equipment])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')
            ->withArgs(fn ($probaArray, $number, $funcDaedalus) => (
                $probaArray instanceof ProbaCollection
                && $probaArray->toArray() === ['communication_center' => 1]
                && $number === 1
                && $funcDaedalus === $daedalus
            ))
            ->andReturn([$equipment])
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => (
                $event->getStatusHolder() === $equipment
                && $event->getStatusName() === EquipmentStatusEnum::BROKEN))
            ->once()
        ;

        $broken = $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        $this->isTrue($equipment->isBroken());
        $this->assertEquals(1, $broken);
    }

    public function testEquipmentBreakAlreadyBrokenEvent()
    {
        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution(['communication_center' => 1]);

        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);

        $daedalus = new Daedalus();

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $place = new Place();
        $place->setDaedalus($daedalus);
        $equipment = new GameEquipment($place);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $brokenStatus = new Status($equipment, $brokenConfig);

        $this->gameEquipmentRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->withArgs(['communication_center', $daedalus])
            ->andReturn([$equipment])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')
            ->andReturn([$equipment])
            ->never()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => (
                $event->getStatusHolder() === $equipment
                && $event->getStatusName() === EquipmentStatusEnum::BROKEN))
            ->never()
        ;

        $broken = $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        $this->assertEquals(0, $broken);
    }

    public function testNotBreakingGameItems()
    {
        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution(['communication_center' => 1]);

        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);

        $daedalus = new Daedalus();

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->randomService->shouldReceive('poissonRandom')->andReturn(0)->once();

        $broken = $this->service->handleEquipmentBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $broken);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $place = new Place();
        $place->setDaedalus($daedalus);
        $equipment = new GameEquipment($place);
        $item = new GameItem($place);

        $this->gameEquipmentRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->withArgs(['communication_center', $daedalus])
            ->andReturn([$equipment])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomDaedalusEquipmentFromProbaCollection')
            ->withArgs(fn ($probaArray, $number, $funcDaedalus) => (
                $probaArray instanceof ProbaCollection
                && $probaArray->toArray() === ['communication_center' => 1]
                && $number === 1
                && $funcDaedalus === $daedalus
            ))
            ->andReturn([$equipment])
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => (
                $event->getStatusHolder() === $equipment
                && $event->getStatusName() === EquipmentStatusEnum::BROKEN))
            ->once()
        ;

        $broken = $this->service->handleEquipmentBreak($daedalus, new \DateTime());

        $this->assertEquals(1, $broken);
    }

    public function testHandleDoorBreakEvents()
    {
        $this->randomService->shouldReceive('poissonRandom')->andReturn(0)->once();

        $broken = $this->service->handleDoorBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $broken);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $place = new Place();
        $place->setDaedalus(new Daedalus());
        $door = new Door($place);
        $door->setRooms(new ArrayCollection([new Place(), new Place()]));
        $door->setName('Door');

        $this->gameEquipmentRepository
            ->shouldReceive('findByCriteria')
            ->withArgs(fn (GameEquipmentCriteria $criteria) => $criteria->getInstanceOf() === [Door::class])
            ->andReturn([$door])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$door])
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => (
                $event->getStatusHolder() === $door
                && $event->getStatusName() === EquipmentStatusEnum::BROKEN))
            ->once()
        ;

        $broken = $this->service->handleDoorBreak(new Daedalus(), new \DateTime());

        $this->assertEquals(1, $broken);
    }

    public function testHandlePanicCrisisEvents()
    {
        $daedalus = new Daedalus();

        $panicCrisis = $this->service->handlePanicCrisis($daedalus, new \DateTime());

        $this->assertEquals(0, $panicCrisis);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $daedalus = new Daedalus();
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $daedalus->addPlayer($player);
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (PlayerEvent $event) => $event->getPlayer() === $player)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$player])
            ->once()
        ;

        $broken = $this->service->handlePanicCrisis($daedalus, new \DateTime());

        $this->assertEquals(1, $broken);
    }

    public function testHandlePanicCrisisEventsMushNotConcerned()
    {
        $this->randomService->shouldReceive('poissonRandom')->andReturn(2)->once();

        $daedalus = new Daedalus();
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $mushPlayer = new Player();
        $mushPlayerInfo = new PlayerInfo($mushPlayer, new User(), new CharacterConfig());
        $mushPlayer->setPlayerInfo($mushPlayerInfo);

        $mushConfig = new StatusConfig();
        $mushConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mush = new Status($mushPlayer, $mushConfig);

        $daedalus->addPlayer($mushPlayer);
        $daedalus->addPlayer($player);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (PlayerEvent $event) => $event->getPlayer() === $player)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->withArgs(fn (array $humans, int $pick) => count($humans) === 1 && in_array($player, $humans))
            ->andReturn([$player])
            ->once()
        ;

        $broken = $this->service->handlePanicCrisis($daedalus, new \DateTime());

        $this->assertEquals(1, $broken);
    }

    public function testHandleMetalPlatesEvents()
    {
        $metalPlates = $this->service->handleMetalPlates(new Daedalus(), new \DateTime());

        $this->assertEquals(0, $metalPlates);

        $this->randomService->shouldReceive('poissonRandom')->andReturn(1)->once();

        $daedalus = new Daedalus();
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $daedalus->addPlayer($player);
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (PlayerEvent $event) => $event->getPlayer() === $player)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$player])
            ->once()
        ;

        $metalPlates = $this->service->handleMetalPlates($daedalus, new \DateTime());

        $this->assertEquals(1, $metalPlates);
    }
}
