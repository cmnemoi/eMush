<?php

namespace Mush\Room\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Room\Entity\Door;
use Mush\Room\Repository\DoorRepository;

class DoorService implements DoorServiceInterface
{
    private EntityManagerInterface $entityManager;

    private DoorRepository $repository;

    /**
     * DoorService constructor.
     * @param EntityManagerInterface $entityManager
     * @param DoorRepository $repository
     */
    public function __construct(EntityManagerInterface $entityManager, DoorRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(Door $door): Door
    {
        $this->entityManager->persist($door);
        $this->entityManager->flush();

        return $door;
    }

    public function findById(int $id): ?Door
    {
        return $this->repository->find($id);
    }
}
