<?php

namespace Mush\Test\Alert\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Repository\AlertRepository;
use Mush\Alert\Service\AlertService;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Equipment;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class AlertServiceTest extends TestCase
{
    private AlertServiceInterface $alertService;

    private EntityManagerInterface|Mockery\Mock $entityManager;

    private AlertRepository|Mockery\Mock $repository;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(AlertRepository::class);

        $this->alertService = new AlertService(
            $this->entityManager,
            $this->repository,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNoOxygenAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(15);

        // oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->oxygenAlert($daedalus);
    }

    public function testOxygenAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(8);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->oxygenAlert($daedalus);
    }

    public function testSolveOxygenAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(9);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::LOW_OXYGEN);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once()
        ;
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->oxygenAlert($daedalus);
    }

    public function testNoHullAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(95);

        // oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->hullAlert($daedalus);
    }

    public function testHullAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(20);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->hullAlert($daedalus);
    }

    public function testSolveHullAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(90);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::LOW_HULL);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once()
        ;
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

        $this->alertService->gravityAlert($daedalus, true);
    }

    public function testRepairGravityAlert()
    {
        $daedalus = new Daedalus();

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::NO_GRAVITY);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY])
            ->andReturn($alert)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->gravityAlert($daedalus, false);
    }

    public function testBrokenEquipmentAlert()
    {
        $daedalus = new Daedalus();

        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameEquipment = new Equipment();
        $gameEquipment->setHolder($room);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;

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
        $gameEquipment = new Door();
        $gameEquipment->addRoom($room);

        $doorElement = new AlertElement();
        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_DOORS)
            ->addAlertElement($doorElement)
        ;

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleEquipmentBreak($gameEquipment);

        $this->assertCount(2, $alert->getAlertElements());
    }

    public function testRepairEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameEquipment = new Equipment();
        $gameEquipment->setHolder($room);

        $equipmentElement1 = new AlertElement();
        $equipmentElement1->setEquipment($gameEquipment);
        $equipmentElement2 = new AlertElement();

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($equipmentElement1)
            ->addAlertElement($equipmentElement2)
        ;

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->with($equipmentElement1)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleEquipmentRepair($gameEquipment);

        $this->assertCount(1, $alert->getAlertElements());
    }

    public function testRepairAllEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameEquipment = new Equipment();
        $gameEquipment->setHolder($room);

        $equipmentElement1 = new AlertElement();
        $equipmentElement1->setEquipment($gameEquipment);

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($equipmentElement1)
        ;

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once()
        ;

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
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->twice();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleFireStart($room);
    }

    public function testStopFireEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $alertElement1 = new AlertElement();
        $alertElement1->setPlace($room);
        $alertElement2 = new AlertElement();

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($alertElement1)
            ->addAlertElement($alertElement2)
        ;

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->with($alertElement1)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleFireStop($room);

        $this->assertCount(1, $alert->getAlertElements());
    }

    public function testStopAllFire()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $alertElement1 = new AlertElement();
        $alertElement1->setPlace($room);

        $alert = new Alert();
        $alert
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($alertElement1)
        ;

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alertElement1)->once();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->twice();

        $this->alertService->handleFireStop($room);
    }

    public function testSatietyAlertActivate()
    {
        $daedalus = new Daedalus();

        $player1 = new Player();
        $player1
            ->setDaedalus($daedalus)
            ->setSatiety(-24)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $player2 = new Player();
        $player2
            ->setDaedalus($daedalus)
            ->setSatiety(-24)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::HUNGER])
            ->andReturn(null)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->handleSatietyAlert($daedalus);
    }

    public function testSatietyAlertAlreadyActive()
    {
        $daedalus = new Daedalus();

        $player1 = new Player();
        $player1
            ->setDaedalus($daedalus)
            ->setSatiety(-24)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $player2 = new Player();
        $player2
            ->setDaedalus($daedalus)
            ->setSatiety(-24)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;

        $alert = new Alert();
        $alert->setName(AlertEnum::HUNGER)->setDaedalus($daedalus);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::HUNGER])
            ->andReturn($alert)
            ->once()
        ;

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();

        $this->alertService->handleSatietyAlert($daedalus);
    }

    public function testSatietyAlertDeactivate()
    {
        $daedalus = new Daedalus();

        $player1 = new Player();
        $player1
            ->setDaedalus($daedalus)
            ->setSatiety(-22)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $player2 = new Player();
        $player2
            ->setDaedalus($daedalus)
            ->setSatiety(-24)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;

        $alert = new Alert();
        $alert->setName(AlertEnum::HUNGER)->setDaedalus($daedalus);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::HUNGER])
            ->andReturn($alert)
            ->once()
        ;

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
            ->once()
        ;

        $alerts = $this->alertService->getAlerts($daedalus);

        $noAlert = new Alert();
        $noAlert->setDaedalus($daedalus)->setName(AlertEnum::NO_ALERT);

        $this->assertEquals(new ArrayCollection([$noAlert]), $alerts);
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
            ->once()
        ;

        $alerts = $this->alertService->getAlerts($daedalus);

        $this->assertEquals(new ArrayCollection([$alert, $alert2]), $alerts);
    }

    public function testFireNorReported()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $room->setDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setName(StatusEnum::FIRE);
        $fireStatus = new Status($room, $fireConfig);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once()
        ;

        $this->assertFalse($this->alertService->isFireReported($room));
    }

    public function testNotValidFire()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $room->setDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setName(StatusEnum::FIRE);
        $fireStatus = new Status($room, $fireConfig);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room)->setPlayer($player);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::FIRES])
            ->andReturn($alert)
            ->once()
        ;

        $this->assertTrue($this->alertService->isFireReported($room));
    }

    public function testValidEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $room->setDaedalus($daedalus);

        $gameEquipment = new Equipment();
        $gameEquipment->setHolder($room);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setName(EquipmentStatusEnum::BROKEN);
        $status = new Status($gameEquipment, $brokenConfig);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once()
        ;

        $this->assertFalse($this->alertService->isEquipmentReported($gameEquipment));
    }

    public function testNotValidEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $room->setDaedalus($daedalus);

        $gameEquipment = new Equipment();
        $gameEquipment->setHolder($room);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setName(EquipmentStatusEnum::BROKEN);
        $status = new Status($gameEquipment, $brokenConfig);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment)->setPlace($room)->setPlayer($player);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $this->repository->shouldReceive('findOneBy')
            ->with(['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS])
            ->andReturn($alert)
            ->once()
        ;

        $this->assertTrue($this->alertService->isEquipmentReported($gameEquipment));
    }
}
