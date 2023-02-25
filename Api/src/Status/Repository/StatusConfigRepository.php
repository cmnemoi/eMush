<?php

namespace Mush\Status\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Status\Entity\Config\StatusConfig;

/**
 * @template-extends ServiceEntityRepository<StatusConfig>
 */
class StatusConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusConfig::class);
    }
}
