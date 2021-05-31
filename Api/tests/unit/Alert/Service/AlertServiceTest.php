<?php

namespace Mush\Test\Alert\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Repository\AlertRepository;
use Mush\Alert\Service\AlertService;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
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
}
