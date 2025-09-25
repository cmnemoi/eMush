<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\ClosedDaedalus;

/**
 * @template-extends ServiceEntityRepository<ClosedDaedalus>
 */
final class ClosedDaedalusRepository extends ServiceEntityRepository implements ClosedDaedalusRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClosedDaedalus::class);
    }

    public function save(ClosedDaedalus $closedDaedalus): void
    {
        $this->getEntityManager()->persist($closedDaedalus);
        $this->getEntityManager()->flush();
    }

    public function findOneByIdOrThrow(int $id): ClosedDaedalus
    {
        $closedDaedalus = $this->findOneBy(['id' => $id]);
        if (!$closedDaedalus) {
            throw new \RuntimeException("ClosedDaedalus {$id} not found");
        }

        return $closedDaedalus;
    }
}
