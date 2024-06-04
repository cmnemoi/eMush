<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Normalizer;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
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

    #[DataProvider('normalizedProjectsdataProvider')]
    public function shouldNormalizeProject(FunctionalTester $I, Example $example): void
    {
        // given I have a project
        $project = $this->daedalus->getProjectByName(ProjectName::from($example['projectKey']));

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project, null, ['currentPlayer' => $this->chun]);

        // then I should get the normalized project
        $I->assertEqualsIgnoringCase(
            expected: [
                'id' => $project->getId(),
                'key' => $example['projectKey'],
                'name' => $example['projectName'],
                'description' => $example['projectDescription'],
                'lore' => $example['projectLore'],
                'progress' => '0%',
                'efficiency' => $example['projectEfficiency'],
                'efficiencyTooltipHeader' => 'Efficacité',
                'efficiencyTooltipText' => 'Pour garder une efficacité optimale, alternez le travail avec un autre collègue.',
                'bonusSkills' => $example['projectBonusSkills'],
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

    #[DataProvider('normalizedProjectsdataProvider')]
    public function shouldNormalizeProjectInDaedalusNormalizationContext(FunctionalTester $I, Example $example): void
    {
        // given I have a
        $project = $this->daedalus->getProjectByName(ProjectName::from($example['projectKey']));

        // when I normalize the project in daedalus normalization context
        $normalizedProject = $this->projectNormalizer->normalize($project, null, [
            'currentPlayer' => $this->chun,
            'normalizing_daedalus' => true,
        ]);

        // then I should get the normalized project
        $I->assertEquals(
            expected: [
                'type' => 'Projet',
                'key' => $example['projectKey'],
                'name' => $example['projectName'],
                'description' => $example['projectDescription'],
                'lore' => $example['projectLore'],
            ],
            actual: $normalizedProject
        );
    }

    public function shouldNormalizeProjectWithPilgredAndNeronCoreInTheRoom(FunctionalTester $I): void
    {
        // given I have magnetic net project
        $project = $this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET);

        // given there is a pilgred terminal in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the auxiliary terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->terminal
        );

        $participateActionId = $this->terminal->getMechanicActionByNameOrThrow(ActionEnum::PARTICIPATE)->getId();

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
                        'id' => $participateActionId,
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
                        'actionProvider' => ['class' => $this->terminal->getClassName(), 'id' => $this->terminal->getId()],
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }

    private function normalizedProjectsdataProvider(): array
    {
        return [
            [
                'projectKey' => 'bric_broc',
                'projectName' => 'Rafistolage Général',
                'projectDescription' => 'À chaque cycle, le Daedalus a 15% de chances de ne pas partir en miette...',
                'projectLore' => 'Quelqu\'un a pensé à REVISSER les plaques de tôles ?',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
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
            ],
            [
                'projectKey' => 'magnetic_net',
                'projectName' => 'Filet magnétique',
                'projectDescription' => 'Lorsque le Daedalus se déplace les Patrouilleurs sont automatiquement ramenés à bord.',
                'projectLore' => 'Un des grands apports de Magellan était que l\'arche stellaire pouvait traîner sa flotte. Vous débuguez les derniers écueils du programme Magnet de pilotage magnétique. Ça va le faire.',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
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
            ],
            [
                'projectKey' => 'icarus_antigrav_propeller',
                'projectName' => 'Propulseurs antigrav',
                'projectDescription' => 'Augmente vos chances de réussites pour atterrir sur des planètes.',
                'projectLore' => 'Grâce à ces propulseurs anti-gravité, les manoeuvres de décollage et d\'atterissage de l\'Icarus deviennent un jeu d\'enfant. L\'entrée dans l\'atmosphère devient d\'une simplicité incroyable. Bref l\'Icarus, un vaisseau qu\'il est bien à faire voler !',
                'projectEfficiency' => 'Efficacité : 12-18%',
                'projectBonusSkills' => [
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
            ],
            [
                'projectKey' => 'fission_coffee_roaster',
                'projectName' => 'Torréfacteur à fission',
                'projectDescription' => 'La machine à café se régénère également au cycle 4.',
                'projectLore' => 'Yeehaa !! En branchant un bête accélérateur de particules portatif sur l\'évacuation de la pompe du torréfacteur, il est désormais possible d\'obtenir un café goûtu en moins de 12h !!',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
                    [
                        'key' => 'caffeine_junkie',
                        'name' => 'Caféinomane',
                        'description' => 'Le Caféinomane travaille deux fois mieux que ses collègues tant qu\'il a accès à la machine à café.//:point: +2:pa: lorsqu\'il consomme un **Café**.//:point: Bonus pour développer certains **Projets NERON**.',
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
            ],
            [
                'projectKey' => 'armour_corridor',
                'projectName' => 'Coursives blindées',
                'projectDescription' => 'Chaque attaque subie par le Daedalus est diminuée d\'un point.',
                'projectLore' => 'Eurêka ! Dans Magellan, les fibres optiques qui longent les coursives peuvent se compresser automatiquement pour pouvoir rajouter facilement des câbles. En injectant des câbles en trop, vous pouvez créer une pseudo armure !',
                'projectEfficiency' => 'Efficacité : 3-4%',
                'projectBonusSkills' => [
                    [
                        'key' => 'technician',
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.//
        :point: +1 :pa_eng: (point d\'action **Réparation**) par jour.//
        :point: Peut **Démonter** des objets.//
        :point: Chances de réussites doublées pour les **Réparations**.//
        :point: Chances de réussites doublées pour les **Rénovations**.//
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
            ],
        ];
    }
}
