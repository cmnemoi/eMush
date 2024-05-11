<?php

namespace Mush\Action\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Action\Entity\ActionConfig;

/**
 * @template-extends ServiceEntityRepository<ActionConfig>
 */
class ActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionConfig::class);
    }
}
