<?php

namespace Mush\Game\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Entity\TriumphConfig;

/**
 * @template-extends ServiceEntityRepository<TriumphConfig>
 */
class TriumphConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TriumphConfig::class);
    }
}
