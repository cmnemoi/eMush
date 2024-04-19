<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\UseCase;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;
use Mush\Project\Factory\ProjectConfigFactory;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CreateProjectFromConfigForDaedalusUseCaseCest extends AbstractFunctionalTest
{
    private CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createProjectFromConfigForDaedalusUseCase = $I->grabService(CreateProjectFromConfigForDaedalusUseCase::class);
    }

    public function shouldCreateProject(FunctionalTester $I): void
    {
        // given I have a project config
        $projectConfig = ProjectConfigFactory::createPlasmaShieldConfig();
        $I->haveInRepository($projectConfig);

        // when I create a project from the config for the Daedalus
        $this->createProjectFromConfigForDaedalusUseCase->execute($projectConfig, $this->daedalus);

        // then the project is created as expected
        $project = $I->grabEntityFromRepository(Project::class, ['config' => $projectConfig]);
        $this->assertProjectIsCreatedAsExpected($I, $project);

        // then the project is added to the Daedalus
        $I->assertContains($project, $this->daedalus->getAvailableProjects());
    }

    private function assertProjectIsCreatedAsExpected(FunctionalTester $I, Project $project): void
    {
        $I->assertNotNull($project);
        $I->assertNotNull($project->getId());
        $I->assertEquals(expected: ProjectName::PLASMA_SHIELD, actual: $project->getName());
        $I->assertEquals(expected: ProjectType::NERON_PROJECT, actual: $project->getType());
        $I->assertEquals(expected: 1, actual: $project->getEfficiency());
        $I->assertEquals(expected: [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN], actual: $project->getBonusSkills());
        $I->assertEquals(expected: $this->daedalus, actual: $project->getDaedalus());
    }
}
