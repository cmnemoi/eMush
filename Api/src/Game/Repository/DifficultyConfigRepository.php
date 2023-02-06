<?php

namespace Mush\Game\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Entity\DifficultyConfig;

/**
 * @template-extends ServiceEntityRepository<DifficultyConfig>
 */
class DifficultyConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifficultyConfig::class);
    }
}
