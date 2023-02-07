<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;

/**
 * @template-extends ServiceEntityRepository<SymptomActivationRequirement>
 */
class SymptomActivationRequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SymptomActivationRequirement::class);
    }
}
