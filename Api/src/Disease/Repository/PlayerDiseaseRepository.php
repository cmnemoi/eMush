<?php

declare(strict_types=1);

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Disease\Entity\PlayerDisease;

/**
 * @template-extends ServiceEntityRepository<PlayerDisease>
 */
final class PlayerDiseaseRepository extends ServiceEntityRepository implements PlayerDiseaseRepositoryInterface
{
    private EntityManager $entityManger;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerDisease::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function save(PlayerDisease $playerDisease): void
    {
        $this->entityManager->persist($playerDisease);
        $this->entityManager->flush();
    }

    public function delete(PlayerDisease $playerDisease): void
    {
        $this->entityManager->remove($playerDisease);
        $this->entityManager->flush();
    }
}
