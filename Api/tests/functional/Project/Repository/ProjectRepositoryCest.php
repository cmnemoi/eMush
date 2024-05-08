<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Repository;

use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Repository\ProjectRepository;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectRepositoryCest extends AbstractFunctionalTest
{
    private ProjectRepository $projectRepository;
    private CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectRepository = $I->grabService(ProjectRepository::class);
        $this->createProjectFromConfigForDaedalusUseCase = $I->grabService(CreateProjectFromConfigForDaedalusUseCase::class);
    }

    public function shouldFindByName(FunctionalTester $I): void
    {
        // given I have a project
        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, [
            'name' => ProjectName::PLASMA_SHIELD,
        ]);

        $this->createProjectFromConfigForDaedalusUseCase->execute(
            $projectConfig,
            $this->daedalus
        );

        /** @var Project $project */
        $project = $this->daedalus->getAllAvailableProjects()->first();

        // when I find the project by name
        $foundProject = $this->projectRepository->findByName($project->getName());

        // then the project is found
        $I->assertEquals($project, $foundProject);
    }

    public function shouldClear(FunctionalTester $I): void
    {
        // given I have a project
        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, [
            'name' => ProjectName::PLASMA_SHIELD,
        ]);

        $this->createProjectFromConfigForDaedalusUseCase->execute(
            $projectConfig,
            $this->daedalus
        );

        // when I clear the project repository
        $this->projectRepository->clear();

        // then the project cannot be found
        $I->assertNull($this->projectRepository->findByName($projectConfig->getName()->value));
    }
}
