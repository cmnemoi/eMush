<?php

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\NeronVersion;
use Mush\Daedalus\Entity\Daedalus;

final class NeronVersionRepository extends ServiceEntityRepository implements NeronVersionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NeronVersion::class);
    }

    public function findByDaedalusIdOrThrow(int $daedalusId): NeronVersion
    {
        $neronVersion = $this->hydrate($this->findOneBy(['daedalus' => $daedalusId]));
        if ($neronVersion === null) {
            throw new \RuntimeException("NERON version for daedalus with id {$daedalusId} not found");
        }

        return $neronVersion;
    }

    public function save(NeronVersion $neronVersion): void
    {
        $entityManager = $this->getEntityManager();

        $neronVersion->setDaedalus($entityManager->getReference(Daedalus::class, $neronVersion->getDaedalusId()));

        $entityManager->persist($neronVersion);
        $entityManager->flush();
    }

    private function hydrate(?NeronVersion $neronVersion): ?NeronVersion
    {
        if ($neronVersion === null) {
            return null;
        }

        $neronVersion->setDaedalusId($neronVersion->getDaedalus()->getId());

        return $neronVersion;
    }
}
