<?php

declare(strict_types=1);

namespace Api\Tests\Unit\Project\Usecase;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ProjectConfig
{
    public function __construct(
        private string $name,
        private string $type,
        private int $efficiency,
        private array $bonusSkills,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEfficiency(): int
    {
        return $this->efficiency;
    }

    public function getBonusSkills(): array
    {
        return $this->bonusSkills;
    }
}

class Project
{
    public function __construct(
        private ProjectConfig $config
    ) {}

    public function getName(): string
    {
        return $this->config->getName();
    }

    public function getType(): string
    {
        return $this->config->getType();
    }

    public function getEfficiency(): int
    {
        return $this->config->getEfficiency();
    }

    public function getBonusSkills(): array
    {
        return $this->config->getBonusSkills();
    }
}

interface ProjectRepositoryInterface
{
    public function findByName(string $name): ?Project;

    public function save(Project $project): void;
}

final class InMemoryProjectRepository implements ProjectRepositoryInterface
{
    private ArrayCollection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function findByName(string $name): ?Project
    {
        return $this->projects->filter(static fn (Project $project) => $project->getName() === $name)->first() ?: null;
    }

    public function save(Project $project): void
    {
        $id = \count($this->projects) + 1;
        $this->projects[$id] = $project;
    }
}

final class CreateProjectFromConfigUsecase
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository
    ) {}

    public function execute(ProjectConfig $projectConfig): void
    {
        $project = new Project($projectConfig);
        $this->projectRepository->save($project);
    }
}

/**
 * @internal
 */
final class CreateProjectFromConfigUsecaseTest extends TestCase
{
    public function testShouldCreateProject(): void
    {
        // given I have a ProjectConfig
        $projectConfig = new ProjectConfig(
            name: 'plasma_shield',
            type: 'NERON_PROJECT',
            efficiency: 1,
            bonusSkills: ['physicist', 'engineer'],
        );

        // given I have a project repository
        $projectRepository = new InMemoryProjectRepository();

        // when I execute the usecase
        $usecase = new CreateProjectFromConfigUsecase($projectRepository);
        $usecase->execute($projectConfig);

        // then the project should be created as expected
        $project = $projectRepository->findByName('plasma_shield');
        self::assertNotNull($project);
        self::assertProjectIsAsExpected($project, $projectConfig);
    }

    private static function assertProjectIsAsExpected(Project $project, ProjectConfig $projectConfig): void
    {
        self::assertEquals($projectConfig->getName(), $project->getName());
        self::assertEquals($projectConfig->getType(), $project->getType());
        self::assertEquals($projectConfig->getEfficiency(), $project->getEfficiency());
        self::assertEquals($projectConfig->getBonusSkills(), $project->getBonusSkills());
    }
}
