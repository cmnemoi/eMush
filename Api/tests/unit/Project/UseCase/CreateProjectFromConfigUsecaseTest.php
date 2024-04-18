<?php

declare(strict_types=1);

namespace Api\Tests\Unit\Project\UseCase;

use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\CreateProjectFromConfigUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateProjectFromConfigUsecaseTest extends TestCase
{
    public function testShouldCreateProject(): void
    {
        // given I have a ProjectConfig
        $projectConfig = new ProjectConfig(
            name: ProjectName::PLASMA_SHIELD,
            type: 'NERON_PROJECT',
            efficiency: 1,
            bonusSkills: ['physicist', 'engineer'],
        );

        // given I have a project repository
        $projectRepository = new InMemoryProjectRepository();

        // when I execute the usecase
        $usecase = new CreateProjectFromConfigUseCase($projectRepository);
        $usecase->execute($projectConfig);

        // then the project should be created as expected
        $project = $projectRepository->findByName(ProjectName::PLASMA_SHIELD);
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
