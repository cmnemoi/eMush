<?php

namespace Mush\Test\Alert\Service;

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
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use PHPUnit\Framework\TestCase;

class AlertServiceTest extends TestCase
{
    private AlertServiceInterface $alertService;

    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var AlertRepository | Mockery\Mock */
    private AlertRepository $repository;

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
        $daedalus->setOxygen(20);

        //oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->oxygenAlert($daedalus, -5);
    }

    public function testOxygenAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(9);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->oxygenAlert($daedalus, -1);
    }

    public function testSolveOxygenAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(7);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::LOW_OXYGEN);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once()
        ;
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->oxygenAlert($daedalus, 2);
    }

    public function testNoHullAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(100);

        //oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->hullAlert($daedalus, -5);
    }

    public function testHullAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(100);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findOneBy')->once();

        $this->alertService->hullAlert($daedalus, -80);
    }

    public function testSolveHullAlert()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(10);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::LOW_HULL);

        $this->repository->shouldReceive('findOneBy')
            ->andReturn($alert)
            ->once()
        ;
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($alert)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->alertService->hullAlert($daedalus, 80);
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
        $gameEquipment = new GameEquipment();
        $gameEquipment->setPlace($room);

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
        $gameEquipment = new GameEquipment();
        $gameEquipment->setPlace($room);

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
        $gameEquipment = new GameEquipment();
        $gameEquipment->setPlace($room);

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
}
