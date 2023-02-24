<?php

namespace Mush\Game\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Entity\AbstractEventConfig;

/**
 * @template-extends ServiceEntityRepository<AbstractEventConfig>
 */
class EventConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractEventConfig::class);
    }
}
