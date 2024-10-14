<?php

namespace Mush\Project\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Project\Entity\ProjectRequirement;

/**
 * @template-extends ServiceEntityRepository<ProjectRequirementRepository>
 */
class ProjectRequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectRequirement::class);
    }
}
