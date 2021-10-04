<?php

namespace Mush\Test\Status\Service;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Repository\StatusConfigRepository;
use Mush\Status\Repository\StatusRepository;
use Mush\Status\Service\StatusService;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var StatusRepository|Mockery\Mock */
    private StatusRepository $repository;

    /** @var StatusConfigRepository|Mockery\Mock */
    private StatusConfigRepository $configRepository;

    private StatusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(StatusRepository::class);
        $this->configRepository = Mockery::mock(StatusConfigRepository::class);

        $this->service = new StatusService(
            $this->entityManager,
            $this->repository,
            $this->configRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetMostRecent()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $item1 = new GameItem();
        $item1->setPlace($room)->setName('item 1');
        $item2 = new GameItem();
        $item2->setPlace($room)->setName('item 2');
        $item3 = new GameItem();
        $item3->setPlace($room)->setName('item 3');

        $hidden1 = new Status($item1);
        $hidden1
            ->setName('hidden')
            ->setCreatedAt(new DateTime());

        $hidden2 = new Status($item3);
        $hidden2
            ->setName('hidden')
            ->setCreatedAt(new DateTime());

        $hidden3 = new Status($item2);
        $hidden3
            ->setName('hidden')
            ->setCreatedAt(new DateTime());

        $mostRecent = $this->service->getMostRecent('hidden', new ArrayCollection([$item1, $item2, $item3]));

        $this->assertEquals('item 2', $mostRecent->getName());
    }

    public function testChangeCharge()
    {
        $gameEquipment = new GameItem();
        $chargeStatus = new ChargeStatus($gameEquipment);

        $chargeStatus
            ->setCharge(4)
            ->setThreshold(6)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, -1);

        $this->assertEquals(3, $chargeStatus->getCharge());

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, -4);

        $this->assertEquals(0, $chargeStatus->getCharge());

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, 7);

        $this->assertEquals(6, $chargeStatus->getCharge());

        $chargeStatus->setAutoRemove(true);

        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $result = $this->service->updateCharge($chargeStatus, -7);

        $this->assertNull($result);
    }
}
