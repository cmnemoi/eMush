<?php

declare(strict_types=1);

namespace Mush\Project\Tests\Functional\Service;

use Mush\Modifier\Entity\GameModifier;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Service\DeactivateProjectService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeactivateProjectServiceCest extends AbstractFunctionalTest
{
    private DeactivateProjectService $deactivateProjectService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->deactivateProjectService = $I->grabService(DeactivateProjectService::class);
    }

    public function shouldDeleteAllProjectModifiersWhenDeactivated(FunctionalTester $I): void
    {
        $project = $this->givenFinishedPlasmaShieldProject($I);

        $this->whenProjectIsDeactivated($project);

        $this->thenAllProjectModifiersShouldBeDeleted($project, $I);
    }

    private function givenFinishedPlasmaShieldProject(FunctionalTester $I): Project
    {
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->player,
            I: $I
        );

        return $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
    }

    private function whenProjectIsDeactivated(Project $project): void
    {
        $this->deactivateProjectService->execute($project);
    }

    private function thenAllProjectModifiersShouldBeDeleted(Project $project, FunctionalTester $I): void
    {
        foreach ($project->getAllModifierConfigs() as $modifierConfig) {
            $I->dontSeeInRepository(GameModifier::class, [
                'modifierConfig' => $modifierConfig,
            ]);
        }
    }
}
