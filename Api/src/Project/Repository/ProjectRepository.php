<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;

/**
 * @template-extends ServiceEntityRepository<Daedalus>
 */
final class ProjectRepository extends ServiceEntityRepository implements ProjectRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function clear(): void
    {
        $this->entityManager->clear();
    }

    public function findByName(ProjectName $name): ?Project
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Project::class, 'project');
        $rsm->addJoinedEntityResult(ProjectConfig::class, 'project_config', 'project', 'config');

        $query = $this->entityManager->createNativeQuery('SELECT * FROM project INNER JOIN project_config ON project.config_id = project_config.id WHERE project_config.name = :name', $rsm);
        $query->setParameter('name', $name);

        return $query->getOneOrNullResult();
    }

    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }
}
