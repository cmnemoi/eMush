<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Player\Entity\ClosedPlayer;

/**
 * @template-extends ServiceEntityRepository<ClosedPlayer>
 */
class ClosedPlayerRepository extends ServiceEntityRepository implements ClosedPlayerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClosedPlayer::class);
    }

    public function save(ClosedPlayer $closedPlayer): void
    {
        $this->getEntityManager()->persist($closedPlayer);
        $this->getEntityManager()->flush();
    }
}
