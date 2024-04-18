<?php

declare(strict_types=1);

namespace Api\Tests\Unit\Project\UseCase;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\CreateProjectFromConfigUseCase;
use Mush\Tests\functional\Project\Factory\ProjectConfigFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateProjectFromConfigUseCaseTest extends TestCase
{
    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function before(): void
    {
        $this->projectRepository = new InMemoryProjectRepository();
    }

    /**
     * @after
     */
    public function after(): void
    {
        $this->projectRepository->clear();
    }

    public function testShouldCreateProject(): void
    {
        // given I have a ProjectConfig
        $projectConfig = ProjectConfigFactory::createPlasmaShieldConfig();

        // when I execute the usecase
        $usecase = new CreateProjectFromConfigUseCase($this->projectRepository);
        $usecase->execute($projectConfig);

        // then the project should be created as expected
        $project = $this->projectRepository->findByName(ProjectName::PLASMA_SHIELD);
        self::assertNotNull($project);
        self::assertProjectIsAsExpected($project, $projectConfig);
    }

    private static function assertProjectIsAsExpected(Project $project): void
    {
        self::assertEquals(expected: ProjectName::PLASMA_SHIELD, actual: $project->getName());
        self::assertEquals(expected: ProjectType::NERON_PROJECT, actual: $project->getType());
        self::assertEquals(expected: 1, actual: $project->getEfficiency());
        self::assertEquals(expected: [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN], actual: $project->getBonusSkills());
    }
}
