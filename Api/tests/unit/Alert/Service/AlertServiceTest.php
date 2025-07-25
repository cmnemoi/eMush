<?php

namespace Mush\Tests\unit\Alert\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Repository\AlertElementRepository;
use Mush\Alert\Repository\AlertRepository;
use Mush\Alert\Service\AlertService;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class AlertServiceTest extends TestCase
{
    private AlertServiceInterface $alertService;

    private EntityManagerInterface|Mockery\Mock $entityManager;
    private AlertElementRepository|Mockery\Mock $alertElementRepository;
    private AlertRepository|Mockery\Mock $repository;
    private LoggerInterface|Mockery\Mock $logger;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->alertElementRepository = \Mockery::mock(AlertElementRepository::class);
        $this->repository = \Mockery::mock(AlertRepository::class);
        $this->logger = \Mockery::mock(LoggerInterface::class);

        $this->alertService = new AlertService(
            $this->entityManager,
            $this->alertElementRepository,
            $this->repository,
            $this->logger
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testNoOxygenAlert()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitOxygen(15);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        // oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->oxygenAlert($daedalus);
    }

    public function testOxygenAlert()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitOxygen(8);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->oxygenAlert($daedalus);
    }

    public function testSolveOxygenAlert()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitOxygen(9);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::LOW_OXYGEN);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once();
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->oxygenAlert($daedalus);
    }

    public function testNoHullAlert()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitHull(95);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        // oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->hullAlert($daedalus);
    }

    public function testHullAlert()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitHull(20);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->hullAlert($daedalus);
    }

    public function testSolveHullAlert()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitHull(90);

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::LOW_HULL);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once();
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->hullAlert($daedalus);
    }

    public function testGravityAlert()
    {
        $daedalus = new Daedalus();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->gravityAlert($daedalus, AlertEnum::BREAK);
    }

    public function testRepairGravityAlert()
    {
        $daedalus = new Daedalus();

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::NO_GRAVITY);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->gravityAlert($daedalus, AlertEnum::REPAIR);
    }

    public function testRebootGravityAlert()
    {
        $daedalus = new Daedalus();

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::GRAVITY_REBOOT);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::GRAVITY_REBOOT])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->gravityAlert($daedalus, AlertEnum::GRAVITY_REBOOT);
    }

    public function testBrokenEquipmentAlert()
    {
        $daedalus = new Daedalus();

        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameEquipment = new GameEquipment($room);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once();
        $this->alertElementRepository->shouldReceive('findOneBy')->once();

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleEquipmentBreak($gameEquipment);
    }

    public function testAddBrokenDoorAlert()
    {
        $daedalus = new Daedalus();

        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameEquipment = new Door($room);
        $gameEquipment->addRoom($room);

        $doorElement = new AlertElement();
        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_DOORS)
            ->addAlertElement($doorElement);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once();
        $this->alertElementRepository->shouldReceive('findOneBy')->once();

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleEquipmentBreak($gameEquipment);

        self::assertCount(2, $alert->getAlertElements());
    }

    public function testRepairEquipment()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $gameEquipment = GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::REACTOR_LATERAL, $room);

        $equipmentElement1 = new AlertElement();
        $equipmentElement1->setEquipment($gameEquipment);
        $equipmentElement2 = new AlertElement();

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($equipmentElement1)
            ->addAlertElement($equipmentElement2);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->with($equipmentElement1)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleEquipmentRepair($gameEquipment);

        self::assertCount(1, $alert->getAlertElements());
    }

    public function testRepairAllEquipment()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $gameEquipment = GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::REACTOR_LATERAL, $room);

        $equipmentElement1 = new AlertElement();
        $equipmentElement1->setEquipment($gameEquipment);

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($equipmentElement1);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($equipmentElement1)->once();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleEquipmentRepair($gameEquipment);
    }

    public function testFireStartAlert()
    {
        $daedalus = new Daedalus();

        $room = new Place();
        $room->setDaedalus($daedalus);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once();
        $this->alertElementRepository->shouldReceive('findOneBy')->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->handleFireStart($room);
    }

    public function testStopFireEquipment()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        $alertElement1 = new AlertElement();
        $alertElement1->setPlace($room);
        $alertElement2 = new AlertElement();

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($alertElement1)
            ->addAlertElement($alertElement2);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->with($alertElement1)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleFireStop($room);

        self::assertCount(1, $alert->getAlertElements());
    }

    public function testStopAllFire()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        $alertElement1 = new AlertElement();
        $alertElement1->setPlace($room);

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($alertElement1);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alertElement1)->once();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleFireStop($room);
    }

    public function testSatietyAlertActivate()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setInitSatiety(-24);

        $daedalus = new Daedalus();

        $player1 = new Player();
        $player1
            ->setDaedalus($daedalus)
            ->setPlayerVariables($characterConfig);
        $playerInfo1 = new PlayerInfo($player1, new User(), new CharacterConfig());
        $player1->setPlayerInfo($playerInfo1);

        $player2 = new Player();
        $player2
            ->setDaedalus($daedalus)
            ->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, new User(), new CharacterConfig());
        $player2->setPlayerInfo($playerInfo2);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::HUNGER])
            ->andReturn(null)
            ->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->handleSatietyAlert($daedalus);
    }

    public function testSatietyAlertAlreadyActive()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setInitSatiety(-24);

        $daedalus = new Daedalus();

        $player1 = new Player();
        $player1
            ->setDaedalus($daedalus)
            ->setPlayerVariables($characterConfig);
        $playerInfo1 = new PlayerInfo($player1, new User(), new CharacterConfig());
        $player1->setPlayerInfo($playerInfo1);

        $player2 = new Player();
        $player2
            ->setDaedalus($daedalus)
            ->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player1, new User(), new CharacterConfig());
        $player2->setPlayerInfo($playerInfo2);

        $alert = new Alert();
        $alert->setName(AlertEnum::HUNGER)->setDaedalus($daedalus);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::HUNGER])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();

        $this->alertService->handleSatietyAlert($daedalus);
    }

    public function testSatietyAlertDeactivate()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setInitSatiety(-24);

        $daedalus = new Daedalus();

        $player1 = new Player();
        $player1
            ->setDaedalus($daedalus)
            ->setPlayerVariables($characterConfig)
            ->setSatiety(-22);
        $playerInfo1 = new PlayerInfo($player1, new User(), new CharacterConfig());
        $player1->setPlayerInfo($playerInfo1);

        $player2 = new Player();
        $player2
            ->setDaedalus($daedalus)
            ->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, new User(), new CharacterConfig());
        $player2->setPlayerInfo($playerInfo2);

        $alert = new Alert();
        $alert->setName(AlertEnum::HUNGER)->setDaedalus($daedalus);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::HUNGER])
            ->andReturn($alert)
            ->once();

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->handleSatietyAlert($daedalus);
    }

    public function getNoAlertTest()
    {
        $daedalus = new Daedalus();

        $this->repository->shouldReceive('findBy')
            ->with(['daedalus' => $daedalus])
            ->andReturn(null)
            ->once();

        $alerts = $this->alertService->getAlerts($daedalus);

        $noAlert = new Alert();
        $noAlert->setDaedalus($daedalus)->setName(AlertEnum::NO_ALERT);

        self::assertSame(new ArrayCollection([$noAlert]), $alerts);
    }

    public function getAlertsTest()
    {
        $daedalus = new Daedalus();

        $alert = new Alert();
        $alert->setName(AlertEnum::HUNGER)->setDaedalus($daedalus);
        $alert2 = new Alert();
        $alert2->setDaedalus($daedalus)->setName(AlertEnum::NO_GRAVITY);

        $this->repository->shouldReceive('findBy')
            ->with(['daedalus' => $daedalus])
            ->andReturn([$alert, $alert2])
            ->once();

        $alerts = $this->alertService->getAlerts($daedalus);

        self::assertSame(new ArrayCollection([$alert, $alert2]), $alerts);
    }

    public function testFireNorReported()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room);
        $room->setDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setStatusName(StatusEnum::FIRE);
        $fireStatus = new Status($room, $fireConfig);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once();

        self::assertFalse($this->alertService->isFireReported($room));
    }

    public function testNotValidFire()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $room->setDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setStatusName(StatusEnum::FIRE);
        $fireStatus = new Status($room, $fireConfig);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room)->setPlayerInfo($playerInfo);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once();

        self::assertTrue($this->alertService->isFireReported($room));
    }

    public function testValidEquipment()
    {
        $daedalus = new Daedalus();
        $room = Place::createRoomByNameInDaedalus(RoomEnum::ALPHA_BAY, $daedalus);
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room);
        $room->setDaedalus($daedalus);

        $gameEquipment = GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::REACTOR_LATERAL, $room);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($gameEquipment, $brokenConfig);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once();

        self::assertFalse($this->alertService->isEquipmentReported($gameEquipment));
    }

    public function testNotValidEquipment()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $room = $daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $room->setDaedalus($daedalus);

        $gameEquipment = GameEquipmentFactory::createEquipmentByNameForHolder(EquipmentEnum::REACTOR_LATERAL, $room);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($gameEquipment, $brokenConfig);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment)->setPlace($room)->setPlayerInfo($playerInfo);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once();

        self::assertTrue($this->alertService->isEquipmentReported($gameEquipment));
    }

    public function testDoNotCreateDuplicateFireAlertElement()
    {
        // GIVEN two distinct PHP objects representing the same Room (same id)
        $daedalus = new Daedalus();

        $place1 = new Place();
        $place1->setDaedalus($daedalus);
        $place2 = new Place();
        $place2->setDaedalus($daedalus);

        // Force identical database identifier to simulate re-hydration across requests
        $reflection = new \ReflectionProperty(Place::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($place1, 1);
        $reflection->setValue($place2, 1);

        // Existing alert element for the first instance of the room
        $alertElement = new AlertElement();
        $alertElement->setPlace($place1);

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($alertElement);

        // No persistence should occur because the alert element already exists
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('flush')->never();

        // WHEN retrieving the alert element with a different PHP object but same id
        $returnedElement = $this->alertService->getAlertFireElement($alert, $place2);

        // THEN the service must return the already existing element and not create a duplicate
        self::assertSame($alertElement, $returnedElement);
        self::assertCount(1, $alert->getAlertElements());
    }
}
