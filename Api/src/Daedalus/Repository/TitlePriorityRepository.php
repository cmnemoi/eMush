<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\TitlePriority;

/**
 * @template-extends ServiceEntityRepository<TitlePriority>
 */
final class TitlePriorityRepository extends ServiceEntityRepository implements TitlePriorityRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TitlePriority::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function save(TitlePriority $titlePriority): void
    {
        $this->entityManager->persist($titlePriority);
        $this->entityManager->flush();
    }
}
