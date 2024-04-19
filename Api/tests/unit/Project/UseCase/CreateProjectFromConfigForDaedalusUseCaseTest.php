<?php

declare(strict_types=1);

namespace Mush\Tests\Unit\Project\UseCase;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryDaedalusRepository;
use Mush\Game\Enum\SkillEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Factory\ProjectConfigFactory;
use Mush\Project\Repository\InMemoryProjectRepository;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateProjectFromConfigForDaedalusUseCaseTest extends TestCase
{
    private InMemoryDaedalusRepository $daedalusRepository;
    private InMemoryProjectRepository $projectRepository;

    /**
     * @before
     */
    public function before(): void
    {
        $this->daedalusRepository = new InMemoryDaedalusRepository();
        $this->projectRepository = new InMemoryProjectRepository();
    }

    /**
     * @after
     */
    public function after(): void
    {
        $this->daedalusRepository->clear();
        $this->projectRepository->clear();
    }

    public function testShouldCreateProject(): void
    {
        // given I have a ProjectConfig
        $projectConfig = ProjectConfigFactory::createPlasmaShieldConfig();

        // given I have a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // when I execute the usecase
        $createProjectFromConfigForDaedalusUseCase = new CreateProjectFromConfigForDaedalusUseCase(
            $this->daedalusRepository,
            $this->projectRepository
        );
        $createProjectFromConfigForDaedalusUseCase->execute($projectConfig, $daedalus);

        // then the project should be created as expected
        $project = $this->projectRepository->findByName(ProjectName::PLASMA_SHIELD);
        self::assertNotNull($project);
        self::assertProjectIsAsExpected($project, $projectConfig);
        self::assertEquals(expected: $daedalus, actual: $project->getDaedalus());

        // then Daedalus should have the project
        self::assertNotEmpty($daedalus->getAvailableProjects()->filter(static fn (Project $project) => $project->getName() === ProjectName::PLASMA_SHIELD));
    }

    private static function assertProjectIsAsExpected(Project $project): void
    {
        self::assertEquals(expected: ProjectName::PLASMA_SHIELD, actual: $project->getName());
        self::assertEquals(expected: ProjectType::NERON_PROJECT, actual: $project->getType());
        self::assertEquals(expected: 1, actual: $project->getEfficiency());
        self::assertEquals(expected: [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN], actual: $project->getBonusSkills());
    }
}
