<?php

namespace Mush\Equipment\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Repository\EquipmentRepository;

class EquipmentService implements EquipmentServiceInterface
{
    private EquipmentRepository $repository;
    private EntityManagerInterface $entityManager;
    /**
     * EquipmentService constructor.
     */
    public function __construct(
        EquipmentRepository $repository,
        EntityManagerInterface $entityManager
    )
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function findById(int $id): ?Equipment
    {
        $equipment = $this->repository->find($id);

        return $equipment instanceof Equipment ? $equipment : null;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): EquipmentConfig
    {
        return $this->repository->findByNameAndDaedalus($name, $daedalus);
    }

    public function persist(Equipment $equipment): Equipment
    {
        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return $equipment;
    }

    public function delete(Equipment $equipment): void
    {
        $this->entityManager->remove($equipment);
        $this->entityManager->flush();
    }

}
