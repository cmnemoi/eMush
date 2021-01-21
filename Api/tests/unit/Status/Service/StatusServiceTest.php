<?php

namespace Mush\Test\Status\Service;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusService;
use Mush\Status\Repository\StatusRepository;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var StatusRepository | Mockery\Mock */
    private StatusRepository $repository;

    private StatusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(StatusRepository::class);

        $this->service = new StatusService(
            $this->entityManager,
            $this->repository
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
        $room = new Room();

        $item1 = new GameItem();
        $item1->setRoom($room)->setName('item 1');
        $item2 = new GameItem();
        $item2->setRoom($room)->setName('item 2');
        $item3 = new GameItem();
        $item3->setRoom($room)->setName('item 3');

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
}
