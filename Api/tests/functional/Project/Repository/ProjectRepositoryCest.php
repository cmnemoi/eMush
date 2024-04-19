<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project\Repository;

use Mush\Project\Entity\Project;
use Mush\Project\Repository\ProjectRepository;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\unit\Project\ProjectConfigFactory;

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

    public function shouldFindByName(FunctionalTester $I)
    {
        // given I have a project
        $projectConfig = ProjectConfigFactory::createPlasmaShieldConfig();
        $I->haveInRepository($projectConfig);

        $this->createProjectFromConfigForDaedalusUseCase->execute(
            $projectConfig,
            $this->daedalus
        );

        /** @var Project $project */
        $project = $this->daedalus->getAvailableProjects()->first();

        // when I find the project by name
        $foundProject = $this->projectRepository->findByName($project->getName());

        // then the project is found
        $I->assertEquals($project, $foundProject);
    }
}
