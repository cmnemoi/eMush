<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Normalizer\ProjectNormalizer;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class ProjectNormalizerForNeronProjectsCest extends AbstractFunctionalTest
{
    private ProjectNormalizer $projectNormalizer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private int $participateActionId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectNormalizer = $I->grabService(ProjectNormalizer::class);
        $this->projectNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given NERON's core terminal in the room
        $pilgredTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $pilgredTerminal
        );

        $this->participateActionId = $pilgredTerminal->getMechanicActionByNameOrThrow(ActionEnum::PARTICIPATE)->getId();
    }

    public function shouldNormalizeBricBrocProject(FunctionalTester $I): void
    {
        // given I have Bric Broc project
        $project = $this->daedalus->getProjectByName(ProjectName::BRIC_BROC);

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project, null, ['currentPlayer' => $this->chun]);

        // then I should get the normalized project
        $I->assertEqualsIgnoringCase(
            expected: [
                'id' => $project->getId(),
                'key' => 'bric_broc',
                'name' => 'Rafistolage Général',
                'description' => 'À chaque cycle, le Daedalus a 15% de chances de ne pas partir en miette...',
                'lore' => 'Quelqu\'un a pensé à REVISSER les plaques de tôles ?',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 6-9%',
                'efficiencyTooltipHeader' => 'Efficacité',
                'efficiencyTooltipText' => 'Pour garder une efficacité optimale, alternez le travail avec un autre collègue.',
                'bonusSkills' => [
                    [
                        'key' => 'conceptor',
                        'name' => 'Concepteur',
                        'description' => 'Le Concepteur dispose de deux actions gratuites chaque jour pour utiliser le Cœur de NERON.//:point: +2 :pa_core: (points d\'action **Conception**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'creative',
                        'name' => 'Créatif',
                        'description' => 'Parfois il faut savoir renverser le problème.//:point: Chacun de vos échecs sur une action payante a 50% de chances de vous rendre *1* :pa:.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
                'actions' => [
                    [
                        'id' => $this->participateActionId,
                        'key' => ActionEnum::PARTICIPATE,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'specialistPointCosts' => [],
                        'successRate' => 100,
                        'description' => 'Avance le Projet en fonction de vos capacités.',
                        'canExecute' => true,
                        'confirmation' => null,
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }

    public function shouldNormalizeBricBrocProjectInDaedalusNormalizationContext(FunctionalTester $I): void
    {
        // given I have Bric Broc project
        $project = $this->daedalus->getProjectByName(ProjectName::BRIC_BROC);

        // when I normalize the project in daedalus normalization context
        $normalizedProject = $this->projectNormalizer->normalize($project, null, [
            'currentPlayer' => $this->chun,
            'normalizing_daedalus' => true,
        ]);

        // then I should get the normalized project
        $I->assertEquals(
            expected: [
                'type' => 'Projet',
                'key' => 'bric_broc',
                'name' => 'Rafistolage Général',
                'description' => 'À chaque cycle, le Daedalus a 15% de chances de ne pas partir en miette...',
                'lore' => 'Quelqu\'un a pensé à REVISSER les plaques de tôles ?',
            ],
            actual: $normalizedProject
        );
    }
}
