<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\ComManagerAnnouncement;

/**
 * @template-extends ServiceEntityRepository<ComManagerAnnouncement>
 */
final class ComManagerAnnouncementRepository extends ServiceEntityRepository implements ComManagerAnnouncementRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComManagerAnnouncement::class);
    }

    public function findByIdOrThrow(int $id): ComManagerAnnouncement
    {
        return $this->find($id) ?? throw new \RuntimeException("ComManagerAnnouncement {$id} not found");
    }

    public function save(ComManagerAnnouncement $comManagerAnnouncement): void
    {
        $this->_em->persist($comManagerAnnouncement);
        $this->_em->flush();
    }
}
