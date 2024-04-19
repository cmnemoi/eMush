<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
        $sql = 'SELECT project.* FROM project INNER JOIN project_config ON project.config_id = project_config.id WHERE project_config.name = :name';

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(Project::class, 'project');
        $rsm->addJoinedEntityFromClassMetadata(ProjectConfig::class, 'project_config', 'project', 'config', ['id' => 'config_id']);

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('name', $name->value);

        return $query->getOneOrNullResult();
    }

    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }
}
