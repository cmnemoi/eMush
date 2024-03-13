<?php

declare(strict_types=1);

namespace Mush\Exploration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Player\Entity\Player;

/**
 * @template-extends ServiceEntityRepository<ClosedExploration>
 */
final class ClosedExplorationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClosedExploration::class);
    }

    public function getMostRecentForPlayer(Player $player): ?ClosedExploration
    {
        $playerDaedalus = $player->getDaedalus()->getDaedalusInfo();

        $queryBuilder = $this->createQueryBuilder('closedExploration');
        $queryBuilder
            ->innerJoin('closedExploration.daedalusInfo', 'daedalus')
            ->where('daedalus = :daedalus')
            ->setParameter('daedalus', $playerDaedalus)
            ->orderBy('closedExploration.createdAt', 'DESC')
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
