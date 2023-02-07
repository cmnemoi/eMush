<?php

namespace Mush\Modifier\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;

/**
 * @template-extends ServiceEntityRepository<ModifierActivationRequirement>
 */
class ModifierActivationRequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModifierActivationRequirement::class);
    }
}
