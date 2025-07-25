<?php

declare(strict_types=1);

namespace Mush\Project\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Project\Entity\Project;

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
        $this->entityManager->getConnection()->executeStatement('DELETE FROM project');
    }

    public function findByName(string $name): ?Project
    {
        $sql = 'SELECT project.* FROM project INNER JOIN project_config ON project.config_id = project_config.id WHERE project_config.name = :name';

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(Project::class, 'project');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('name', $name);

        return $query->getOneOrNullResult();
    }

    public function lockAndRefresh(Project $project, int $mode = LockMode::PESSIMISTIC_WRITE): Project
    {
        $this->entityManager->lock($project, $mode);
        $this->entityManager->refresh($project);

        return $project;
    }

    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }
}
