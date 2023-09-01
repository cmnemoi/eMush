<?php

namespace Mush\Alert\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Alert\Entity\AlertElement;

/**
 * @template-extends ServiceEntityRepository<AlertElement>
 */
class AlertElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlertElement::class);
    }
}
