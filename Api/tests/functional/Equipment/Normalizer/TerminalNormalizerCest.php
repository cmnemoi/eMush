<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\TranslationService;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerService;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class TerminalNormalizerCest extends AbstractFunctionalTest
{
    private TerminalNormalizer $terminalNormalizer;
    private TranslationService $translationService;
    private PlayerService $playerService;
    private NormalizerInterface $normalizer;
    private Project $pilgredProject;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private string $isChunPresentText;
    private string $isAnyMushDeadText;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(NormalizerInterface::class);
        $this->terminalNormalizer = $I->grabService(TerminalNormalizer::class);
        $this->terminalNormalizer->setNormalizer($this->normalizer);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->translationService = $I->grabService(TranslationService::class);

        $this->pilgredProject = $this->daedalus->getPilgred();
        $this->isChunPresentText = $this->translationService->translate(
            key: 'research_laboratory.chun_present',
            parameters: [],
            domain: 'terminal',
            language: $this->daedalus->getLanguage(),
        );
        $this->isAnyMushDeadText = $this->translationService->translate(
            key: 'research_laboratory.mush_dead',
            parameters: [],
            domain: 'terminal',
            language: $this->daedalus->getLanguage(),
        );
        $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);

        $this->givenGameHasStarted();
    }

    public function testShouldNormalizePilgredTerminal(FunctionalTester $I): void
    {
        // given I have a PILGRED terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->chun]);

        // then I should get the normalized terminal
        $I->assertEquals(expected: ProjectName::PILGRED->value, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Réacteur PILGRED', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: 'La remise en état de PILGRED demande des compétences énormes. Il vous faudra vous armer de patience et survivre pour y aboutir. Une fois réparé, la possibilité de revenir sur Sol vous sera ouverte. Qu\'en ferez-vous ?',
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: ['exit_terminal'],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertArrayHasKey('projects', $normalizedTerminal);
        $normalizedPilgredProject = $normalizedTerminal['projects'][0];
        $I->assertEquals(expected: 'PILGRED', actual: $normalizedPilgredProject['name']);
    }

    public function testShouldNormalizeExtraInfosWhenPilgredIsFinished(FunctionalTester $I): void
    {
        // given PILGRED is finished
        $this->pilgredProject->makeProgressAndUpdateParticipationDate(100);

        // given I have a PILGRED terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->chun]);

        // then I should get the normalized terminal
        $I->assertEquals(expected: ProjectName::PILGRED->value, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Réacteur PILGRED', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: 'La remise en état de PILGRED demande des compétences énormes. Il vous faudra vous armer de patience et survivre pour y aboutir. Une fois réparé, la possibilité de revenir sur Sol vous sera ouverte. Qu\'en ferez-vous ?',
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: ['exit_terminal'],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertArrayHasKey('projects', $normalizedTerminal);
        $normalizedPilgredProject = $normalizedTerminal['projects'][0];
        $I->assertEquals(expected: 'PILGRED', actual: $normalizedPilgredProject['name']);

        $I->assertEquals(expected: 'PILGRED est pleinement opérationnel.', actual: $normalizedTerminal['infos']['pilgredFinishedDescription']);
    }

    public function testShouldNormalizeNeronCoreTerminal(FunctionalTester $I): void
    {
        // given I have 3 proposed NERON projects
        $this->daedalus->getProjectByName(ProjectName::HEAT_LAMP)->propose();
        $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD)->propose();
        $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER)->propose();

        // given I have 1 not proposed NERON project
        $this->daedalus->getProjectByName(ProjectName::FIRE_SENSOR);

        // given I have a NERON's core terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->chun]);

        // then I should get the normalized terminal
        $I->assertEquals(expected: EquipmentEnum::NERON_CORE, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Cœur de NERON', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: "Vous êtes dans le Cœur de NERON. Ici vous pouvez le mettre à jour et **débloquer des fonctionnalités** avancées bénéfiques pour tout l'équipage. Ces fonctionnalités font partie du projet original Magellan.////Les projets avanceront mieux si vous possédez **les compétences adéquates**.////Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. **Le travail alterné avec un camarade est la clé !**////Et ce n'est pas tout : un seul projet par set peut être mené à fin. **Lorsqu'un projet est complété, les deux autres sont désactivés de manière permanente.** Discutez entre vous pour déterminer lequel servira le vaisseau au mieux!",
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: ['exit_terminal'],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertArrayHasKey('projects', $normalizedTerminal);
        $I->assertFalse($normalizedTerminal['infos']['noProposedNeronProjects']);
        $I->assertCount(expectedCount: 3, haystack: $normalizedTerminal['projects']);
    }

    public function testShouldNormalizeWithExtraInfosIfThereAreNoProposedProjects(FunctionalTester $I): void
    {
        // given I have 1 not proposed NERON project
        $this->daedalus->getProjectByName(ProjectName::FIRE_SENSOR);

        // given I have a NERON's core terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->chun]);

        // then I should get the normalized terminal
        $I->assertEquals(expected: EquipmentEnum::NERON_CORE, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Cœur de NERON', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: "Vous êtes dans le Cœur de NERON. Ici vous pouvez le mettre à jour et **débloquer des fonctionnalités** avancées bénéfiques pour tout l'équipage. Ces fonctionnalités font partie du projet original Magellan.////Les projets avanceront mieux si vous possédez **les compétences adéquates**.////Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. **Le travail alterné avec un camarade est la clé !**////Et ce n'est pas tout : un seul projet par set peut être mené à fin. **Lorsqu'un projet est complété, les deux autres sont désactivés de manière permanente.** Discutez entre vous pour déterminer lequel servira le vaisseau au mieux!",
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: ['exit_terminal'],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertArrayHasKey('projects', $normalizedTerminal);
        $I->assertCount(expectedCount: 0, haystack: $normalizedTerminal['projects']);
        $I->assertTrue($normalizedTerminal['infos']['noProposedNeronProjects']);
        $I->assertEquals(expected: 'Il n\'y a plus de projets à faire aboutir.', actual: $normalizedTerminal['infos']['noProposedNeronProjectsDescription']);
    }

    public function testShouldNormalizeAuxiliaryTerminal(FunctionalTester $I): void
    {
        // given I have 3 proposed NERON projects
        $this->daedalus->getProjectByName(ProjectName::HEAT_LAMP)->propose();
        $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD)->propose();
        $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER)->propose();

        // given I have 1 not proposed NERON project
        $this->daedalus->getProjectByName(ProjectName::FIRE_SENSOR);

        // given I have a NERON's core terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::AUXILIARY_TERMINAL,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->chun]);

        // then I should get the normalized terminal
        $I->assertEquals(expected: EquipmentEnum::AUXILIARY_TERMINAL, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Cœur de NERON auxiliaire', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: "Vous êtes dans le Cœur de NERON. Ici vous pouvez le mettre à jour et **débloquer des fonctionnalités** avancées bénéfiques pour tout l'équipage. Ces fonctionnalités font partie du projet original Magellan.////Les projets avanceront mieux si vous possédez **les compétences adéquates**.////Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. **Le travail alterné avec un camarade est la clé !**////Et ce n'est pas tout : un seul projet par set peut être mené à fin. **Lorsqu'un projet est complété, les deux autres sont désactivés de manière permanente.** Discutez entre vous pour déterminer lequel servira le vaisseau au mieux!",
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: ['exit_terminal'],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertArrayHasKey('projects', $normalizedTerminal);
        $I->assertFalse($normalizedTerminal['infos']['noProposedNeronProjects']);
        $I->assertCount(expectedCount: 3, haystack: $normalizedTerminal['projects']);
    }

    public function shouldNormalizeBiosTerminal(FunctionalTester $I): void
    {
        // given I have a BIOS terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BIOS_TERMINAL,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        $plasmaShieldProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $plasmaShieldProject->finish();
        $magneticNetProject = $this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET);
        $magneticNetProject->finish();

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize(
            $terminal,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        // then I should get the normalized terminal
        $I->assertEquals(expected: EquipmentEnum::BIOS_TERMINAL, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Terminal BIOS', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: 'Vous accédez au BIOS de NERON. Seul son administrateur peut modifier la configuration.',
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: [
                ActionEnum::EXIT_TERMINAL->value,
                ActionEnum::CHANGE_NERON_CPU_PRIORITY->value,
                ActionEnum::CHANGE_NERON_CREW_LOCK->value,
                ActionEnum::CHANGE_NERON_FOOD_DESTRUCTION_OPTION->value,
                ActionEnum::TOGGLE_DEATH_ANNOUNCEMENTS->value,
                ActionEnum::TOGGLE_MAGNETIC_NET->value,
                ActionEnum::TOGGLE_NERON_INHIBITION->value,
                ActionEnum::TOGGLE_PLASMA_SHIELD->value,
                ActionEnum::TOGGLE_VOCODED_ANNOUNCEMENTS->value,
            ],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertEquals(
            expected: [
                'cpu_priority_name' => 'Priorité CPU',
                'cpu_priority_description' => 'Contrôle la répartition des tranches CPU. Soit elle rend les projets ou recherches plus faciles à réaliser, soit elle accroît le nombre de GeoDonnées fournies par les analyses de planètes, soit les CPU sont en mode paresseux et ne favorisent rien du tout.//Cette propriété ne peut être changée qu\'*une fois par jour et par personne* pour ne pas entraîner une surcharge cognitive de NERON.',
                'crew_lock_name' => 'Verrou équipage',
                'crew_lock_description' => ':point: Verrouillage Pilotage : Les non-pilotes ne sont pas autorisés à piloter un Patrouilleur/Icarus.//:point: Verrouillage Projet : les non-concepteurs ne sont pas autorisés à participer au développement des projets.//:point: Verrouillage Recherche : les non-biologistes et non-médecins ne sont pas autorisés à participer à la Recherche scientifique.',
                'plasma_shield_name' => 'Bouclier Plasma',
                'plasma_shield_description' => 'Active le bouclier plasma.',
                'magnetic_net_name' => 'Filet magnétique',
                'magnetic_net_description' => 'Active le filet magnétique qui permet d\'abandonner les Patrouilleurs à leur triste sort. Ou pas...',
                'neron_inhibition_name' => 'Entrave DMZ-CorePeace',
                'neron_inhibition_description' => 'Active l\'inhibiteur de comportements de NERON. L\'inhibiteur permet de changer un certain nombre de comportements de NERON concernant l\'agressivité.',
                'vocoded_announcements_name' => 'Annonces vocodées',
                'vocoded_announcements_description' => 'Active la possibilité pour NERON de porter les messages de l\'Administrateur NERON.',
                'death_announcements_name' => 'Signaler les décès',
                'death_announcements_description' => 'Active la possibilité pour NERON de signaler les décès avec une annonce.',
                'food_destruction_option_name' => 'Destruction des Denrées',
                'food_destruction_option_description' => 'Règle la destruction programmée des denrées périmées qui s\'enclenche à chaque changement de jour.',
            ],
            actual: $normalizedTerminal['sectionTitles']
        );
        $I->assertEquals(
            expected: [],
            actual: $normalizedTerminal['buttons']
        );
        $I->assertEquals(
            expected: [],
            actual: $normalizedTerminal['projects']
        );
        $I->assertEquals(
            expected: [
                'availableCpuPriorities' => [
                    ['key' => 'none', 'name' => 'Aucune'],
                    ['key' => 'astronavigation', 'name' => 'Astronavigation'],
                    ['key' => 'projects', 'name' => 'Projets'],
                    ['key' => 'research', 'name' => 'Recherche'],
                ],
                'currentCpuPriority' => 'none',
                'crewLocks' => [
                    ['key' => 'projects', 'name' => 'Projets'],
                    ['key' => 'piloting', 'name' => 'Pilotage'],
                    ['key' => 'research', 'name' => 'Recherche'],
                ],
                'currentCrewLock' => 'piloting',
                'plasmaShieldToggles' => [
                    ['key' => 'activate', 'name' => 'Activer'],
                    ['key' => 'deactivate', 'name' => 'Désactiver'],
                ],
                'isPlasmaShieldActive' => false,
                'magneticNetToggles' => [
                    ['key' => 'active', 'name' => 'Actif'],
                    ['key' => 'inactive', 'name' => 'Inactif'],
                ],
                'isMagneticNetActive' => true,
                'isNeronInhibited' => true,
                'neronInhibitionToggles' => [
                    ['key' => 'active', 'name' => 'Oui'],
                    ['key' => 'inactive', 'name' => 'Non'],
                ],
                'areVocodedAnnouncementsActive' => false,
                'vocodedAnnouncementsToggles' => [
                    ['key' => 'active', 'name' => 'Autorisées'],
                    ['key' => 'inactive', 'name' => 'Pas autorisées'],
                ],
                'areDeathAnnouncementsActive' => true,
                'deathAnnouncementsToggles' => [
                    ['key' => 'active', 'name' => 'Oui'],
                    ['key' => 'inactive', 'name' => 'Non'],
                ],
                'currentFoodDestructionOption' => 'unstable',
                'foodDestructionOptions' => [
                    ['key' => 'unstable', 'name' => 'Instable'],
                    ['key' => 'hazardous', 'name' => 'Avariée'],
                    ['key' => 'decomposing', 'name' => 'Décomposition'],
                    ['key' => 'never', 'name' => 'Jamais'],
                ],
            ],
            actual: $normalizedTerminal['infos']
        );
    }

    public function testWhenNoRequirementThenRequirementsShouldBeEmpty(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenRequirementsAreEmpty($I, $normalizedTerminal);
    }

    public function testWhenChunIsInLabIShouldSeeThatRequirement(FunctionalTester $I): void
    {
        $this->givenChunIsInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenRequiremententsShouldContainOnly($I, $normalizedTerminal, $this->isChunPresentText);
    }

    public function testWhenAMushIsDeadIShouldSeeThatRequirement(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();

        $this->givenAMushIsDead($I);

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenRequiremententsShouldContainOnly($I, $normalizedTerminal, $this->isAnyMushDeadText);
    }

    public function testShouldNormalizeItemsInLaboratoryAndInPlayerInventory(FunctionalTester $I)
    {
        $terminal = $this->givenLabTerminal();

        $kuanTiItemsNames = [ItemEnum::ITRACKIE, ItemEnum::FUEL_CAPSULE, ItemEnum::BLASTER];

        $labItemsNames = [ItemEnum::FUEL_CAPSULE, ItemEnum::MUSH_SAMPLE];

        $this->givenKuanTiHasItemsInInventory($kuanTiItemsNames);

        $this->givenLabHasEquipment($labItemsNames);

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenNormalizedItemsNamesShouldBe($I, $normalizedTerminal, array_merge($kuanTiItemsNames, $labItemsNames));
    }

    public function testShouldNotNormalizeEquipmentInTheLab(FunctionalTester $I)
    {
        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiHasItemsInInventory([]);

        $this->givenLabHasEquipment([EquipmentEnum::CRYO_MODULE]);

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenNormalizedItemsShouldBeEmpty($I, $normalizedTerminal);
    }

    public function testShouldNormalizeHiddenItemsInLab(FunctionalTester $I)
    {
        $this->givenItemIsHidden(ItemEnum::BLASTER);

        $this->givenLabHasEquipment([]);

        $this->givenKuanTiHasItemsInInventory([]);

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $items = $normalizedTerminal['items'];

        $I->assertNotEmpty($items);
    }

    public function shouldNotNormalizePlacePersonalItems(FunctionalTester $I): void
    {
        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiHasItemsInInventory([ItemEnum::WALKIE_TALKIE]);

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->kuanTi->getEquipmentByNameOrThrow(ItemEnum::WALKIE_TALKIE),
            newHolder: $terminal->getPlace(),
        );

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $items = $normalizedTerminal['items'];

        $I->assertEmpty($items);
    }

    public function testWhenNoRequirementIsMetThenShouldOnlySeeAnabolicsAndNarcoticsProject(FunctionalTester $I)
    {
        $this->givenChunIsNotInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
        ]);
    }

    public function testWhenChunIsPresentShouldAddNewProjects(FunctionalTester $I)
    {
        $this->givenChunIsInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::CREATE_MYCOSCAN,
            ProjectName::NARCOTICS_DISTILLER,
        ]);
    }

    public function testWhenAMushIsDeadShouldAddNewProjects(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();

        $this->givenAMushIsDead($I);

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::MERIDON_SCRAMBLER,
            ProjectName::MUSH_RACES,
            ProjectName::NARCOTICS_DISTILLER,
            ProjectName::PATULINE_SCRAMBLER,
        ]);
    }

    #[DataProvider('deadCauseProvider')]
    public function testSomeMushDeadCauseShouldNotUnlockNewProjects(FunctionalTester $I, Example $example): void
    {
        $this->givenChunIsNotInLab();

        $this->givenAMushIsDeadForCause($example['cause'], $I);

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
        ]);
    }

    public function testAliveMushShouldNotUnlockProjects(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();

        $this->convertPlayerToMush($I, $this->kuanTi);

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
        ]);
    }

    public function testWhenMedkitIsInLabShouldAddNewProject(FunctionalTester $I)
    {
        $this->givenChunIsNotInLab();

        $this->givenLabHasEquipment([ToolItemEnum::MEDIKIT]);
        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
            ProjectName::ULTRA_HEALING_POMADE,
        ]);
    }

    public function testWhenMedkitIsInPlayerInventoryShouldAlsoAddNewProject(FunctionalTester $I)
    {
        $this->givenChunIsNotInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiHasItemsInInventory([ToolItemEnum::MEDIKIT]);

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
            ProjectName::ULTRA_HEALING_POMADE,
        ]);
    }

    public function testWhenMedkitIsInOtherPlayerInventoryShouldNotAddNewProject(FunctionalTester $I)
    {
        $this->givenChunIsInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenChunHasItemsInInventory([ToolItemEnum::MEDIKIT]);

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::CREATE_MYCOSCAN,
            ProjectName::NARCOTICS_DISTILLER,
        ]);
    }

    public function testWhenSchrodingerIsInPlayerInventoryShouldAddNewProject(FunctionalTester $I)
    {
        $this->givenChunIsNotInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiHasItemsInInventory([ItemEnum::SCHRODINGER]);

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
            ProjectName::NCC_CONTACT_LENSES,
        ]);
    }

    public function testWhenSchrodingerIsInLabShouldNotAddNewProject(FunctionalTester $I)
    {
        $this->givenChunIsNotInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiHasItemsInInventory([]);

        $this->givenLabHasEquipment([ItemEnum::SCHRODINGER]);

        $this->givenKuanTiIsFocusedInResearchLab($terminal);

        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);

        $this->thenProjectsShouldBe($I, $normalizedTerminal, [
            ProjectName::ANABOLICS,
            ProjectName::NARCOTICS_DISTILLER,
        ]);
    }

    protected function deadCauseProvider(): array
    {
        return [
            ['cause' => EndCauseEnum::QUARANTINE],
            ['cause' => EndCauseEnum::ALIEN_ABDUCTED],
        ];
    }

    private function givenGameHasStarted(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
    }

    private function givenItemIsHidden($itemName)
    {
        $blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::HIDDEN,
            holder: $blaster,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenChunHasItemsInInventory($itemsNames)
    {
        foreach ($itemsNames as $itemName) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $itemName,
                equipmentHolder: $this->chun,
                reasons: [],
                time: new \DateTime()
            );
        }
    }

    private function givenKuanTiHasItemsInInventory($itemsNames)
    {
        foreach ($itemsNames as $itemName) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $itemName,
                equipmentHolder: $this->kuanTi,
                reasons: [],
                time: new \DateTime()
            );
        }
    }

    private function givenLabHasEquipment($equipmentNames)
    {
        foreach ($equipmentNames as $equipmentName) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $equipmentName,
                equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
                reasons: [],
                time: new \DateTime()
            );
        }
    }

    private function givenLabTerminal()
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenChunIsNotInLab()
    {
        $laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        if ($laboratory->isChunIn()) {
            $laboratory->removePlayer($this->chun);
        }

        $this->chun->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET));
    }

    private function givenKuanTiIsFocusedInResearchLab($terminal)
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );
    }

    private function givenChunIsInLab()
    {
        $this->chun->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        if (!$this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->isChunIn()) {
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->addPlayer($this->chun);
        }
    }

    private function givenAMushIsDead($I)
    {
        $this->convertPlayerToMush($I, $this->chun);
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::EXPLORATION,
            time: new \DateTime(),
        );
    }

    private function givenAMushIsDeadForCause(string $cause, FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player);
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: $cause,
            time: new \DateTime(),
        );
    }

    private function whenINormalizeTheTerminalForKuanTi($terminal)
    {
        return $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->kuanTi]);
    }

    private function thenNormalizedItemsNamesShouldBe($I, $normalizedTerminal, $expectedItemsNames)
    {
        $items = $normalizedTerminal['items'];
        $I->assertEquals(
            array_map(static fn ($item) => $item['key'], $items),
            $expectedItemsNames
        );
    }

    private function thenRequirementsAreEmpty($I, $normalizedTerminal)
    {
        $requirements = $normalizedTerminal['infos']['requirements'];
        $I->assertEmpty($requirements);
    }

    private function thenRequiremententsShouldContainOnly(FunctionalTester $I, $normalizedTerminal, $expectedRequirement)
    {
        $requirements = $normalizedTerminal['infos']['requirements'];
        $I->assertContains($expectedRequirement, $requirements);
        $I->assertCount(1, $requirements);
    }

    private function thenNormalizedItemsShouldBeEmpty($I, $normalizedTerminal)
    {
        $items = $normalizedTerminal['items'];
        $I->assertEmpty($items);
    }

    private function thenProjectsShouldBe(FunctionalTester $I, $normalizedTerminal, $expectedProjects)
    {
        $projects = $normalizedTerminal['projects'];
        $actualProjectsNames = array_map(static fn ($project) => $project['key'], $projects);
        $expectedProjectsNames = array_map(static fn ($project) => $project->value, $expectedProjects);
        $I->assertEquals(
            $expectedProjectsNames,
            $actualProjectsNames,
        );
    }
}
