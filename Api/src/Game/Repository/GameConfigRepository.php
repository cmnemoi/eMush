<?php

namespace Mush\Game\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Entity\GameConfig;

/**
 * @template-extends ServiceEntityRepository<GameConfig>
 */
class GameConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameConfig::class);
    }
}
