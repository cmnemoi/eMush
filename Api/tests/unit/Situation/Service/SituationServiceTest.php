<?php

namespace Mush\Test\Situation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Situation\Entity\Situation;
use Mush\Situation\Enum\SituationEnum;
use Mush\Situation\Repository\SituationRepository;
use Mush\Situation\Service\SituationService;
use Mush\Situation\Service\SituationServiceInterface;
use PHPUnit\Framework\TestCase;

class SituationServiceTest extends TestCase
{
    private SituationServiceInterface $situationService;

    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var SituationRepository | Mockery\Mock */
    private SituationRepository $repository;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(SituationRepository::class);

        $this->situationService = new SituationService(
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

    public function testNoOxygenSituation()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(20);

        //oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findByNameAndDaedalus')->once();

        $this->situationService->oxygenSituation($daedalus, -5);
    }

    public function testOxygenSituation()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(9);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findByNameAndDaedalus')->once();

        $this->situationService->oxygenSituation($daedalus, -1);
    }

    public function testSolveOxygenSituation()
    {
        $daedalus = new Daedalus();
        $daedalus->setOxygen(7);

        $situation = new Situation($daedalus, SituationEnum::LOW_OXYGEN, true);

        $this->repository->shouldReceive('findByNameAndDaedalus')
            ->andReturn($situation)
            ->once()
        ;
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($situation)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->situationService->oxygenSituation($daedalus, 2);
    }

    public function testNoHullSituation()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(100);

        //oxygen don't go bellow the threshold of 8 oxygen
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->repository->shouldReceive('findByNameAndDaedalus')->once();

        $this->situationService->hullSituation($daedalus, -5);
    }

    public function testHullSituation()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(100);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->once();
        $this->repository->shouldReceive('findByNameAndDaedalus')->once();

        $this->situationService->hullSituation($daedalus, -80);
    }

    public function testSolveHullSituation()
    {
        $daedalus = new Daedalus();
        $daedalus->setHull(10);

        $situation = new Situation($daedalus, SituationEnum::LOW_HULL, true);

        $this->repository->shouldReceive('findByNameAndDaedalus')
            ->andReturn($situation)
            ->once()
        ;
        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('remove')->with($situation)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->situationService->hullSituation($daedalus, 80);
    }
}
