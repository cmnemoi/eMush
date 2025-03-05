<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;

/**
 * @template-extends ServiceEntityRepository<Hunter>
 */
final class HunterRepository extends ServiceEntityRepository implements HunterRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hunter::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function findByIdOrThrow(int $id): Hunter
    {
        $hunter = $this->find($id);

        if ($hunter === null) {
            throw new \RuntimeException("Hunter not found for id {$id}");
        }

        return $hunter;
    }

    public function findOneByTargetOrThrow(HunterTarget $hunterTarget): Hunter
    {
        $hunter = $this->findOneBy(['target' => $hunterTarget]);

        if ($hunter === null) {
            throw new \RuntimeException("Hunter not found for target {$hunterTarget->getId()}");
        }

        return $hunter;
    }

    public function save(Hunter $hunter): void
    {
        $this->entityManager->persist($hunter);
        $this->entityManager->flush();
    }
}
