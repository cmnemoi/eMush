<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Normalizer;

use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectConfigFactory;
use Mush\Project\Normalizer\ProjectNormalizer;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectNormalizerCest extends AbstractFunctionalTest
{
    private ProjectNormalizer $projectNormalizer;
    private CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectNormalizer = $I->grabService(ProjectNormalizer::class);

        $this->createProjectFromConfigForDaedalusUseCase = $I->grabService(CreateProjectFromConfigForDaedalusUseCase::class);
    }

    public function shouldNormalizeProject(FunctionalTester $I): void
    {
        // given I have a project
        $project = $this->createPilgredProject($I);

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project);

        // then I should get the normalized project
        $I->assertEquals(
            expected: [
                'key' => 'pilgred',
                'name' => 'PILGRED',
                'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 1-1%',
                'bonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d\'action de **réparation de PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'technician',
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.//:point: +1 :pa_eng: (point d\'action **Réparation**) par jour.//:point: Chances de réussites doublées pour les **Réparations**.//:point: Chances de réussites doublées pour les **Rénovations**.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }

    private function createPilgredProject(FunctionalTester $I): Project
    {
        $config = ProjectConfigFactory::createPilgredConfig();
        $I->haveInRepository($config);

        $this->createProjectFromConfigForDaedalusUseCase->execute(
            $config,
            $this->daedalus
        );

        return $this->daedalus->getAvailableProjects()->filter(
            static fn (Project $project) => $project->getName() === ProjectName::PILGRED
        )->first();
    }
}
