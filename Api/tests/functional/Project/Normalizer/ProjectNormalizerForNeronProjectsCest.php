<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\SkillEnum;
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
    private GameEquipment $terminal;
    private int $participateActionId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectNormalizer = $I->grabService(ProjectNormalizer::class);
        $this->projectNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given NERON's core terminal in the room
        $this->terminal = $this->gameEquipmentService->createGameEquipmentFromName(
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
            target: $this->terminal
        );

        $this->participateActionId = $this->terminal->getMechanicActionByNameOrThrow(ActionEnum::PARTICIPATE)->getId();
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
                        'key' => ActionEnum::PARTICIPATE->value,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'specialistPointCosts' => [],
                        'successRate' => 100,
                        'description' => 'Avance le Projet en fonction de vos capacités.',
                        'canExecute' => true,
                        'confirmation' => null,
                        'actionProvider' => ['class' => $this->terminal::class, 'id' => $this->terminal->getId()],
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

    public function shouldNormalizeMagneticNetProject(FunctionalTester $I): void
    {
        // given I have Magnetic Net project
        $project = $this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET);

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project, null, ['currentPlayer' => $this->chun]);

        // then I should get the normalized project
        $I->assertEqualsIgnoringCase(
            expected: [
                'id' => $project->getId(),
                'key' => 'magnetic_net',
                'name' => 'Filet magnétique',
                'description' => 'Lorsque le Daedalus se déplace les Patrouilleurs sont automatiquement ramenés à bord.',
                'lore' => 'Un des grands apports de Magellan était que l\'arche stellaire pouvait traîner sa flotte. Vous débuguez les derniers écueils du programme Magnet de pilotage magnétique. Ça va le faire.',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 6-9%',
                'efficiencyTooltipHeader' => 'Efficacité',
                'efficiencyTooltipText' => 'Pour garder une efficacité optimale, alternez le travail avec un autre collègue.',
                'bonusSkills' => [
                    [
                        'key' => 'pilot',
                        'name' => 'Pilote',
                        'description' => 'Le pilote est un expert en manœuvre dans les vaisseaux Icarus, Pasiphae et Patrouilleur. Sa
                    maîtrise aérienne est impressionnante.
                    //
                    :point: **Chances doublées** de toucher en Patrouilleur.
                    //
                    :point: **Ne rate jamais** les atterrissages et décollages.
                    //
                    :point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques
                    quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des
                    avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d\'action de **réparation de
                    PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
                'actions' => [
                    [
                        'id' => $this->participateActionId,
                        'key' => ActionEnum::PARTICIPATE->value,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'specialistPointCosts' => [],
                        'successRate' => 100,
                        'description' => 'Avance le Projet en fonction de vos capacités.',
                        'canExecute' => true,
                        'confirmation' => null,
                        'actionProvider' => ['class' => $this->terminal::class, 'id' => $this->terminal->getId()],
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }

    public function shouldNormalizeMagneticNetProjectInDaedalusNormalizationContext(FunctionalTester $I): void
    {
        // given I have Magnetic Net project
        $project = $this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET);

        // when I normalize the project in daedalus normalization context
        $normalizedProject = $this->projectNormalizer->normalize($project, null, [
            'currentPlayer' => $this->chun,
            'normalizing_daedalus' => true,
        ]);

        // then I should get the normalized project
        $I->assertEquals(
            expected: [
                'type' => 'Projet',
                'key' => 'magnetic_net',
                'name' => 'Filet magnétique',
                'description' => 'Lorsque le Daedalus se déplace les Patrouilleurs sont automatiquement ramenés à bord.',
                'lore' => 'Un des grands apports de Magellan était que l\'arche stellaire pouvait traîner sa flotte. Vous débuguez les derniers écueils du programme Magnet de pilotage magnétique. Ça va le faire.',
            ],
            actual: $normalizedProject
        );
    }

    public function shouldNormalizeWhosWhoProject(FunctionalTester $I): void
    {
        // given I have Who's Who project
        $project = $this->daedalus->getProjectByName(ProjectName::WHOS_WHO);

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project, null, ['currentPlayer' => $this->chun]);

        // then I should get the normalized project
        $I->assertEqualsIgnoringCase(
            expected: [
                'id' => $project->getId(),
                'key' => 'whos_who',
                'name' => 'Protocle ACTOPI',
                'description' => 'Affiche l\'identité des équipiers sur le Traqueur.',
                'lore' => 'Affiche un pixel de couleur unique qui indique qui est qui à bord sur le traqueur. Pratique pour traquer les moindres faits et gestes de chacun.',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 18-27%',
                'efficiencyTooltipHeader' => 'Efficacité',
                'efficiencyTooltipText' => 'Pour garder une efficacité optimale, alternez le travail avec un autre collègue.',
                'bonusSkills' => [
                    [
                        'key' => 'radio_expert',
                        'name' => 'Expert radio',
                        'description' => 'L\'Expert radio peut décoder les enchevêtrements les plus complexes d\'ondes spatiales afin de
                    reconstituer ou envoyer des messages à l\'autre bout de l\'Univers.//:point: **Double efficacité**
                    pour établir le **contact** vers Sol.//:point: Améliore les tentatives de **contact** de Sol des
                    équipiers dans la même pièce de 50%.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'nurse',
                        'name' => 'Infirmier',
                        'description' => 'L\'Infirmier est toujours prêt à rendre service.//:point: +1:pa_heal: chaque jour (point d\'action **Soin**).//:point: Peut lire les **propriétés des médicaments.**//:point: Bonus pour participer à certains **Projets NERON**.',
                    ],
                ],
                'actions' => [
                    [
                        'id' => $this->participateActionId,
                        'key' => ActionEnum::PARTICIPATE->value,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'specialistPointCosts' => [],
                        'successRate' => 100,
                        'description' => 'Avance le Projet en fonction de vos capacités.',
                        'canExecute' => true,
                        'confirmation' => null,
                        'actionProvider' => ['class' => $this->terminal::class, 'id' => $this->terminal->getId()],
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }
}
