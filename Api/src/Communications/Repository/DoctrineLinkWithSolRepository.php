<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @template-extends ServiceEntityRepository<LinkWithSol>
 */
final class DoctrineLinkWithSolRepository extends ServiceEntityRepository implements LinkWithSolRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkWithSol::class);
    }

    public function findByDaedalusIdOrThrow(int $daedalusId): LinkWithSol
    {
        $linkWithSol = $this->hydrate($this->findOneBy(['daedalus' => $daedalusId]));
        if ($linkWithSol === null) {
            throw new \RuntimeException("LinkWithSol not found for daedalus id {$daedalusId}");
        }

        return $linkWithSol;
    }

    public function save(LinkWithSol $linkWithSol): void
    {
        $entityManager = $this->getEntityManager();

        $linkWithSol->setDaedalus($entityManager->getReference(Daedalus::class, $linkWithSol->getDaedalusId()));

        $entityManager->persist($linkWithSol);
        $entityManager->flush();
    }

    private function hydrate(?LinkWithSol $linkWithSol): ?LinkWithSol
    {
        if ($linkWithSol === null) {
            return null;
        }

        $linkWithSol->setDaedalusId($linkWithSol->getDaedalus()->getId());

        return $linkWithSol;
    }
}
