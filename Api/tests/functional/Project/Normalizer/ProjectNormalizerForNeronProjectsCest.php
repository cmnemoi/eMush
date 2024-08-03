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

    #[DataProvider('normalizedProjectsDataProvider')]
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
                'isLastAdvancedProject' => false,
                'actions' => [
                    [
                        'id' => $this->participateActionId,
                        'key' => ActionEnum::PARTICIPATE->value,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'skillPointCosts' => [],
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

    #[DataProvider('normalizedProjectsDataProvider')]
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
                'isLastAdvancedProject' => false,
                'actions' => [
                    [
                        'id' => $participateActionId,
                        'key' => ActionEnum::PARTICIPATE->value,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'skillPointCosts' => [],
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

    public function shouldNormalizeLastAdvancedProject(FunctionalTester $I): void
    {
        // given I have two projects
        $plasmaShield = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $armouredCorridor = $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR);
        $plasmaShield->propose();
        $armouredCorridor->propose();

        // make them advance
        $plasmaShield->makeProgressAndUpdateParticipationDate(1);
        $armouredCorridor->makeProgressAndUpdateParticipationDate(1);

        // when I normalize the projects
        $normalizedPlasmaShield = $this->projectNormalizer->normalize($plasmaShield, null, ['currentPlayer' => $this->chun]);
        $normalizedArmouredCorridor = $this->projectNormalizer->normalize($armouredCorridor, null, ['currentPlayer' => $this->chun]);

        // then I should see that armoured corridor is the last advanced project
        $I->assertTrue($normalizedArmouredCorridor['isLastAdvancedProject']);

        // and plasma shield is not
        $I->assertFalse($normalizedPlasmaShield['isLastAdvancedProject']);
    }

    private function normalizedProjectsDataProvider(): array
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
                        'description' => 'Parfois il faut savoir renverser le problème.//:point: Chacun de vos échecs sur une action payante a 50% de chances de vous rendre **1** :pa:.//:point: Bonus pour développer certains **Projets NERON**.',
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
                'projectLore' => 'Eurêka ! Dans Magellan, les fibres optiques qui longent les coursives peuvent se compresser automatiquement pour pouvoir rajouter facilement des câbles. En injectant des câbles en trop, vous pouvez créer une pseudo-armure !',
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
            [
                'projectKey' => 'call_of_dirty',
                'projectName' => 'Dynarcade',
                'projectDescription' => 'Rend disponible la Dynarcade en Baie Alpha 2. Permet de se défouler au détriment de quelques contusions. La Dynarcade permet de regagner du moral.',
                'projectLore' => 'La Dynarcade, cette vieille machine de guerre bricolée pour l\'entraînement des bleusailles peut facilement être recyclée en jeu vidéo.',
                'projectEfficiency' => 'Efficacité : 18-27%',
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
                        'key' => 'shooter',
                        'name' => 'Tireur',
                        'description' => 'Le Tireur manipule les armes de tout type avec beaucoup d\'aisance.//:point: +2 **Tirs gratuits** :pa_shoot: par jour.//:point: Expédition : +1 à la force de votre équipe en cas de combat, si vous avez une arme à feu.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'patrolship_blaster_gun',
                'projectName' => 'Canon blaster',
                'projectDescription' => 'Augmente la puissance de feu des Patrouilleurs d\'un point.',
                'projectLore' => 'Un canon de 40mm c\'est bien. Un Blaster de 80, c\'est mieux.',
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
                        'key' => 'shooter',
                        'name' => 'Tireur',
                        'description' => 'Le Tireur manipule les armes de tout type avec beaucoup d\'aisance.//:point: +2 **Tirs gratuits** :pa_shoot: par jour.//:point: Expédition : +1 à la force de votre équipe en cas de combat, si vous avez une arme à feu.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'bay_door_xxl',
                'projectName' => 'Portail de décollage extra-large',
                'projectDescription' => 'Les manœuvres de décollage et d’atterrissage sont beaucoup plus simples.',
                'projectLore' => 'Après de savants calculs, les ingénieurs de la Fédération avaient publié un superbe rapport sur la corrélation entre la taille des portes des baies et la facilité de décollage. Étonnant.',
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
                        'key' => 'technician',
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.//
        :point: +1 :pa_eng: (point d\'action **Réparation**) par jour.//
        :point: Peut **Démonter** des objets.//
        :point: Chances de réussites doublées pour les **Réparations**.//
        :point: Chances de réussites doublées pour les **Rénovations**.//
        :point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'trash_load',
                'projectName' => 'Tas de débris',
                'projectDescription' => 'Génère 4 débris métalliques et 4 débris plastiques en Salle des moteurs.',
                'projectLore' => 'L\'ennemi est trop coriace ? Mettez donc un peu de fer dans les épinards !',
                'projectEfficiency' => 'Efficacité : 18-27%',
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
                        'key' => 'robotics_expert',
                        'name' => 'Robotique',
                        'description' => 'L\'expert en robotique peut créer et manipuler les drones comme bon lui semble.//:point: Commence
                    avec le **plan d\'un drone**.//:point: Peut **améliorer les drones**.//:point: Bonus pour développer
                    certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'extra_drone',
                'projectName' => 'Drone supplémentaire',
                'projectDescription' => 'Ajoute un Drone pour prendre soin du Daedalus dans le Nexus.',
                'projectLore' => 'En récupérant les données des drones de Terrence dans une vieille archive, il devrait être possible de demander à NERON de nous en construire une nouvelle copie.',
                'projectEfficiency' => 'Efficacité : 6-9%',
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
                        'key' => 'robotics_expert',
                        'name' => 'Robotique',
                        'description' => 'L\'expert en robotique peut créer et manipuler les drones comme bon lui semble.//:point: Commence
                    avec le **plan d\'un drone**.//:point: Peut **améliorer les drones**.//:point: Bonus pour développer
                    certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'food_retailer',
                'projectName' => 'Distributeur pneumatique',
                'projectDescription' => 'La nourriture du Jardin est envoyée directement au Réfectoire via le Pneumatique.',
                'projectLore' => 'Oh pinaise... Vous n\'y aviez jamais songé mais cette canalisation vous inspire. Dans certaines sociétés préhistoriques, ils se servaient de tubes aéro induits pour faire passer des messages... Mais on peut aussi s\'en servir pour la nourriture ! Vous transformez la gouttière en tube pneumatique. (Quel intérêt peut avoir une gouttière dans l\'espace...)',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
                    [
                        'key' => 'robotics_expert',
                        'name' => 'Robotique',
                        'description' => 'L\'expert en robotique peut créer et manipuler les drones comme bon lui semble.//:point: Commence
                    avec le **plan d\'un drone**.//:point: Peut **améliorer les drones**.//:point: Bonus pour développer
                    certains **Projets NERON**.',
                    ],

                    [
                        'key' => 'chef',
                        'name' => 'Cuistot',
                        'description' => 'Le cuisinier est un expert redoutable pour préparer des bons petits plats. Il a également un avis éclairé sur tout ce qui se mange ou presque.//:point: Peut lire les **propriétés des aliments**.//:point: Peut lire les **propriétés des fruits**.//:point: +4 :pa_cook: par jour (points d\'action **Cuisine**).//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'auto_return_icarus',
                'projectName' => 'Rapatriement magnétique',
                'projectDescription' => 'L\'Icarus retourne automatiquement dans le Daedalus en cas d\'échec de la mission.',
                'projectLore' => 'En exploitant le code source de NERON, vous vous apercevez que vous pourriez doter l\'Icarus d\'une pseudo-conscience. Vous décidez de virer le code pseudo cognitif et le moteur d\'émotion et de garder le noyau de survie. Maintenant l\'Icarus reviendra si l\'expédition est décimée.',
                'projectEfficiency' => 'Efficacité : 12-18%',
                'projectBonusSkills' => [
                    [
                        'key' => 'it_expert',
                        'name' => 'Informaticien',
                        'description' => 'L\'Informaticien est à l\'aise dès qu\'il passe derrière un écran. Il manie à la perfection les
                    interfaces des terminaux de NERON, d\'astrophysique, et de communication.//:point: +2 :pa_comp: (points
                    d\'action **Informatique**) par jour.//:point: Chances de réussites doublées avec le **Bidouilleur**.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'robotics_expert',
                        'name' => 'Robotique',
                        'description' => 'L\'expert en robotique peut créer et manipuler les drones comme bon lui semble.//:point: Commence
                    avec le **plan d\'un drone**.//:point: Peut **améliorer les drones**.//:point: Bonus pour développer
                    certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'radar_trans_void',
                'projectName' => 'Radar à ondes spatiales',
                'projectDescription' => 'L’antenne stellaire double désormais les progrès des tentatives de connexion avec Sol.',
                'projectLore' => 'Que ne feriez-vous pas pour les beaux yeux de la Comm... En augmentant la priorité CPU du thread Fourier, vous leur rendrez la vie plus facile...',
                'projectEfficiency' => 'Efficacité : 12-18%',
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
                        'key' => 'radio_expert',
                        'name' => 'Expert radio',
                        'description' => 'L\'Expert radio peut décoder les enchevêtrements les plus complexes d\'ondes spatiales afin de
                    reconstituer ou envoyer des messages à l\'autre bout de l\'Univers.//:point: **Double efficacité**
                    pour établir le **contact** vers Sol.//:point: Améliore les tentatives de **contact** de Sol des
                    équipiers dans la même pièce de 50%.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'neron_targeting_assist',
                'projectName' => 'Visée heuristique',
                'projectDescription' => 'Augmente la visée en vol et en tourelle de 25%.',
                'projectLore' => 'NERON peut aussi vous faciliter la vie. Un petit patch de son algorithme de remplissage des tableaux de bords et hop !',
                'projectEfficiency' => 'Efficacité : 3-4%',
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
                        'key' => 'shooter',
                        'name' => 'Tireur',
                        'description' => 'Le Tireur manipule les armes de tout type avec beaucoup d\'aisance.//:point: +2 **Tirs gratuits** :pa_shoot: par jour.//:point: Expédition : +1 à la force de votre équipe en cas de combat, si vous avez une arme à feu.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'whos_who',
                'projectName' => 'Protocle ACTOPI',
                'projectDescription' => 'Affiche l\'identité des équipiers sur le Traqueur.',
                'projectLore' => 'Affiche un pixel de couleur unique qui indique qui est qui à bord sur le traqueur. Pratique pour traquer les moindres faits et gestes de chacun.',
                'projectEfficiency' => 'Efficacité : 18-27%',
                'projectBonusSkills' => [
                    [
                        'key' => 'radio_expert',
                        'name' => 'Expert radio',
                        'description' => 'L\'Expert radio peut décoder les enchevêtrements les plus complexes d\'ondes spatiales afin de
                    reconstituer ou envoyer des messages à l\'autre bout de l\'Univers.//:point: **Double efficacité**
                    pour établir le **contact** vers Sol.//:point: Améliore les tentatives de **contact** de Sol des
                    équipiers dans la même pièce de 50%.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'paranoid',
                        'name' => 'Paranoïaque',
                        'description' => 'Le Paranoïaque pense que tous ses collègues sont potentiellement dangereux, il surveille toujours ses arrières grâce à ses caméras supplémentaires.//:point: Commence avec 2 **Caméras** supplémentaires.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'icarus_lavatory',
                'projectName' => 'Lavabo opportun',
                'projectDescription' => 'Permet de ne pas se salir en revenant d\'expédition.',
                'projectLore' => 'Quel magnifique lavabo inoxydable, vous n\'avez plus d\'excuses pour ne pas vous laver les mains entre chaque mission.',
                'projectEfficiency' => 'Efficacité : 18-27%',
                'projectBonusSkills' => [
                    [
                        'key' => 'biologist',
                        'name' => 'Biologiste',
                        'description' => 'Le Biologiste est plus efficace dans les recherches du laboratoire.//:point: Peut lire les **propriétés des médicaments**.//:point: **+4% de bonus** sur chacune des **Recherches** du Laboratoire.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
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
                ],
            ],
            [
                'projectKey' => 'hydroponic_incubator',
                'projectName' => 'Couveuse hydroponique',
                'projectDescription' => 'Dans le jardin, les jeunes pousses croissent 2 fois plus vite.',
                'projectLore' => 'Grâce à cet aménagement spécialement destiné aux jeunes pousses, on peut saturer leur environnement d\'azote afin d\'accélérer leur développement sans risquer d\'endommager les plants adultes.',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques
                    quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des
                    avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d\'action de **réparation de
                    PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'botanist',
                        'name' => 'Botaniste',
                        'description' => 'Le Botaniste peut distinguer les caractéristiques des fruits et des plantes. Il est également
                    redoutablement efficace dans la maintenance du Jardin.//:point: Peut lire les **propriétés des
                    fruits**.//
                    :point: Peut lire les **propriétés des plantes**.//
                    :point: Peut effectuer des **greffes**.//
                    :point: +2 :pa_garden: par jour (points d\'action **Jardinage**).//
                    :point: Expédition : L\'évènement **Récolte** donne un fruit de plus.//
                    :point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'icarus_larger_bay',
                'projectName' => 'Agrandissement de la cale',
                'projectDescription' => 'L\'Icarus peut transporter 2 personnes supplémentaires.',
                'projectLore' => 'En nous débarrassant d\'un moteur auxiliaire dont personne n\'a réussi à prouver l\'utilité à ce jour, nous pouvons installer deux sièges supplémentaires.',
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
                        'key' => 'technician',
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.//
        :point: +1 :pa_eng: (point d\'action **Réparation**) par jour.//
        :point: Peut **Démonter** des objets.//
        :point: Chances de réussites doublées pour les **Réparations**.//
        :point: Chances de réussites doublées pour les **Rénovations**.//
        :point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'patrol_ship_launcher',
                'projectName' => 'Propulseur de décollage',
                'projectDescription' => 'Les manœuvres de décollage vous coûtent moins de :pa:.',
                'projectLore' => 'En finissant d\'implémenter le noyau des servo-moteurs de la porte, NERON s\'est aperçu qu\'il y en avait sous la piste ! Et ça accélère sacrément les manœuvres...',
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
                'projectKey' => 'neron_project_thread',
                'projectName' => 'Participation de NERON',
                'projectDescription' => 'Si un projet est au-dessus de 0%, il est automatiquement amélioré de 5% à chaque cycle.',
                'projectLore' => 'Après une engueulade NERON devient souvent plus productif, surtout pour bosser sur lui-même. Vous installez un réveil qui insulte sa mémoire vive automatiquement toutes les heures.',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
                    [
                        'key' => 'it_expert',
                        'name' => 'Informaticien',
                        'description' => 'L\'Informaticien est à l\'aise dès qu\'il passe derrière un écran. Il manie à la perfection les
                    interfaces des terminaux de NERON, d\'astrophysique, et de communication.//:point: +2 :pa_comp: (points
                    d\'action **Informatique**) par jour.//:point: Chances de réussites doublées avec le **Bidouilleur**.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'shrink',
                        'name' => 'Psy',
                        'description' => 'Le psy occupe un poste de soutien psychologique. Il permet de garder le moral et soigne les maladies psychologiques.//:point: A chaque cycle, **1 Point de Moral (:pmo:) est régénéré** à chaque personnage allongé dans sa pièce.//:point: **Soigne les maladies Psy**.//:point: Accorde l\'action **Réconforter**, laquelle améliore le moral.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'turret_extra_fire_rate',
                'projectName' => 'Pulsateur inversé',
                'projectDescription' => 'Les tourelles se rechargent deux fois plus vite.',
                'projectLore' => 'En inversant les priorités du modulateur Tesla des tourelles, il est possible de doubler leur cadence !',
                'projectEfficiency' => 'Efficacité : 12-18%',
                'projectBonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques
                    quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des
                    avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d\'action de **réparation de
                    PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
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
                ],
            ],
            [
                'projectKey' => 'quantum_sensors',
                'projectName' => 'Détecteur à ondes de probabilité',
                'projectDescription' => 'Le scanner de planète révèle une section de plus par essai.',
                'projectLore' => 'Les InfraSenseurs disposent aussi d\'un capteur de probabilité de présence de matière noire ce qui devrait notablement améliorer leur rendement.',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
                    [
                        'key' => 'astrophysicist',
                        'name' => 'Astrophysicien',
                        'description' => 'L\'Astrophysicien est habilité à lancer des scans pour trouver des planètes à portée ou
                    déterminer leur composition.//:point: L\'action **Scanner** coûte 1 :pa: de moins.//:point: L\'action **Analyse** révèle une section de planète supplémentaire.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'radio_expert',
                        'name' => 'Expert radio',
                        'description' => 'L\'Expert radio peut décoder les enchevêtrements les plus complexes d\'ondes spatiales afin de
                    reconstituer ou envoyer des messages à l\'autre bout de l\'Univers.//:point: **Double efficacité**
                    pour établir le **contact** vers Sol.//:point: Améliore les tentatives de **contact** de Sol des
                    équipiers dans la même pièce de 50%.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'patrolship_extra_ammo',
                'projectName' => 'Réservoir de Teslatron',
                'projectDescription' => 'Les patrouilleurs disposent de 6 charges supplémentaires.',
                'projectLore' => 'En ajoutant un piston de compression, on peut doubler la capacité de stockage des cuves à Teslatron des patrouilleurs.',
                'projectEfficiency' => 'Efficacité : 6-9%',
                'projectBonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques
                    quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des
                    avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d\'action de **réparation de
                    PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
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
                ],
            ],
            [
                'projectKey' => 'noise_reducer',
                'projectName' => 'Isolateur phonique',
                'projectDescription' => 'Augmente de 2 le nombre de :pa: maximal de chaque équipier.',
                'projectLore' => 'Le bruit infernal des moteurs vous empêche de profiter de votre potentiel ? Il y a une solution pour ça.',
                'projectEfficiency' => 'Efficacité : 3-4%',
                'projectBonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques
                    quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des
                    avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d\'action de **réparation de
                    PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'biologist',
                        'name' => 'Biologiste',
                        'description' => 'Le Biologiste est plus efficace dans les recherches du laboratoire.//:point: Peut lire les **propriétés des médicaments**.//:point: **+4% de bonus** sur chacune des **Recherches** du Laboratoire.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'apero_kitchen',
                'projectName' => 'Cuisine SNC',
                'projectDescription' => 'Améliore la cuisine du Réfectoire.',
                'projectLore' => 'La division R&D de SNC a mis au point l\'outil ultime pour ruiner la productivité. Et on a retrouvé les cartons ! Attention aux projections. APÉRO !',
                'projectEfficiency' => 'Efficacité : 18-27%',
                'projectBonusSkills' => [
                    [
                        'key' => 'chef',
                        'name' => 'Cuistot',
                        'description' => 'Le cuisinier est un expert redoutable pour préparer des bons petits plats. Il a également un avis éclairé sur tout ce qui se mange ou presque.//:point: Peut lire les **propriétés des aliments**.//:point: Peut lire les **propriétés des fruits**.//:point: +4 :pa_cook: par jour (points d\'action **Cuisine**).//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'creative',
                        'name' => 'Créatif',
                        'description' => 'Parfois il faut savoir renverser le problème.//:point: Chacun de vos échecs sur une action payante a 50% de chances de vous rendre **1** :pa:.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'floor_heating',
                'projectName' => 'Chauffage au sol',
                'projectDescription' => 'Diminue de 50% les blessures dues aux actions.',
                'projectLore' => 'Fini le froid stellaire du parquet du Daedalus. Désormais, vous êtes toujours au chaud !',
                'projectEfficiency' => 'Efficacité : 12-18%',
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
                        'key' => 'conceptor',
                        'name' => 'Concepteur',
                        'description' => 'Le Concepteur dispose de deux actions gratuites chaque jour pour utiliser le Cœur de NERON.//:point: +2 :pa_core: (points d\'action **Conception**) par jour.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'parasite_elim',
                'projectName' => 'Nano-Coccinelles',
                'projectDescription' => 'Les jeunes plantes dans le Jardin voient leur durée de croissance diminuée de 4 cycles.',
                'projectLore' => 'Des nano-coccinelles à vapeur. Pourquoi n\'y avoir pas pensé plus tôt !',
                'projectEfficiency' => 'Efficacité : 12-18%',
                'projectBonusSkills' => [
                    [
                        'key' => 'botanist',
                        'name' => 'Botaniste',
                        'description' => 'Le Botaniste peut distinguer les caractéristiques des fruits et des plantes. Il est également
                    redoutablement efficace dans la maintenance du Jardin.//:point: Peut lire les **propriétés des
                    fruits**.//
                    :point: Peut lire les **propriétés des plantes**.//
                    :point: Peut effectuer des **greffes**.//
                    :point: +2 :pa_garden: par jour (points d\'action **Jardinage**).//
                    :point: Expédition : L\'évènement **Récolte** donne un fruit de plus.//
                    :point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'robotics_expert',
                        'name' => 'Robotique',
                        'description' => 'L\'expert en robotique peut créer et manipuler les drones comme bon lui semble.//:point: Commence
                    avec le **plan d\'un drone**.//:point: Peut **améliorer les drones**.//:point: Bonus pour développer
                    certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'oxy_more',
                'projectName' => 'Conduites oxygénées',
                'projectDescription' => 'À chaque cycle, le Daedalus a 20% de chance d\'économiser l\'oxygène de l\'équipage.',
                'projectLore' => 'Des algues qui poussent dans les conduites de climatisation. On pourrait peut-être faire pousser des bananiers rampants aussi non ?',
                'projectEfficiency' => 'Efficacité : 6-9%',
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
                        'key' => 'biologist',
                        'name' => 'Biologiste',
                        'description' => 'Le Biologiste est plus efficace dans les recherches du laboratoire.//:point: Peut lire les **propriétés des médicaments**.//:point: **+4% de bonus** sur chacune des **Recherches** du Laboratoire.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
            [
                'projectKey' => 'beat_box',
                'projectName' => 'Jukebox',
                'projectDescription' => 'Rend disponible un Plan du Jukebox au Nexus. Le Jukebox permet de se relaxer en écoutant de la musique.',
                'projectLore' => 'À chaque cycle, le Jukebox diffuse une chanson différente. Chaque personnage peut gagner 2 :pmo: quand sa chanson passe.',
                'projectEfficiency' => 'Efficacité : 18-27%',
                'projectBonusSkills' => [
                    [
                        'key' => 'radio_expert',
                        'name' => 'Expert radio',
                        'description' => 'L\'Expert radio peut décoder les enchevêtrements les plus complexes d\'ondes spatiales afin de
                    reconstituer ou envoyer des messages à l\'autre bout de l\'Univers.//:point: **Double efficacité**
                    pour établir le **contact** vers Sol.//:point: Améliore les tentatives de **contact** de Sol des
                    équipiers dans la même pièce de 50%.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'creative',
                        'name' => 'Créatif',
                        'description' => 'Parfois il faut savoir renverser le problème.//:point: Chacun de vos échecs sur une action payante a 50% de chances de vous rendre **1** :pa:.//:point: Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ],
        ];
    }
}
