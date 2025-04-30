<?php

declare(strict_types=1);

namespace Mush\Triumph\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Triumph\Entity\TriumphConfig;

/**
 * @template-extends ServiceEntityRepository<TriumphConfig>
 */
final class TriumphConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TriumphConfig::class);
    }
}
