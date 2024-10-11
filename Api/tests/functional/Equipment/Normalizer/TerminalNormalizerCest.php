<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Normalizer;

use Mush\Action\Actions\Hide;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Service\TranslationService;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerService;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
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
    private Hide $hideAction;
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
        $this->hideAction = $I->grabService(Hide::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->translationService = $I->grabService(TranslationService::class);

        $this->pilgredProject = $this->daedalus->getPilgred();
        $this->isChunPresentText = $this->translationService->translate(
            key: 'research_laboratory.chun_present',
            parameters: [],
            domain: 'terminal'
        );
        $this->isAnyMushDeadText = $this->translationService->translate(
            key: 'research_laboratory.mush_dead',
            parameters: [],
            domain: 'terminal'
        );
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
            expected: "Vous êtes dans le Cœur de NERON. Ici vous pouvez le mettre à jour et **débloquer des fonctionnalités** avancées bénéfiques pour tout l'équipage. Ces fonctionnalités font partie du projet original Magellan.////Les projets avanceront mieux si vous possédez **les compétences adéquates**.////Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. **Le travail alterné avec un camarade est la clé !**////Et ce n'est pas tout : si plus d'un projet avance en parallèle, le premier fini annulera les progrès des autres.",
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
        $this->daedalus->getProjectByName(ProjectName::FIRE_SENSOR, $I);

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
            expected: "Vous êtes dans le Cœur de NERON. Ici vous pouvez le mettre à jour et **débloquer des fonctionnalités** avancées bénéfiques pour tout l'équipage. Ces fonctionnalités font partie du projet original Magellan.////Les projets avanceront mieux si vous possédez **les compétences adéquates**.////Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. **Le travail alterné avec un camarade est la clé !**////Et ce n'est pas tout : si plus d'un projet avance en parallèle, le premier fini annulera les progrès des autres.",
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
            expected: "Vous êtes dans le Cœur de NERON. Ici vous pouvez le mettre à jour et **débloquer des fonctionnalités** avancées bénéfiques pour tout l'équipage. Ces fonctionnalités font partie du projet original Magellan.////Les projets avanceront mieux si vous possédez **les compétences adéquates**.////Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. **Le travail alterné avec un camarade est la clé !**////Et ce n'est pas tout : si plus d'un projet avance en parallèle, le premier fini annulera les progrès des autres.",
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
                ActionEnum::TOGGLE_MAGNETIC_NET->value,
                ActionEnum::TOGGLE_NERON_INHIBITION->value,
                ActionEnum::TOGGLE_PLASMA_SHIELD->value,
            ],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertEquals(
            expected: [
                'cpu_priority_name' => 'Priorité CPU',
                'cpu_priority_description' => 'Contrôle la répartition des tranches CPU. Soit elle rend les projets plus faciles à réaliser, soit elle accroît le nombre de GeoDonnées fournies par les analyses de planètes, soit les CPU sont en mode paresseux et ne favorisent rien du tout.//Cette propriété ne peut être changée qu\'*une fois par jour et par personne* pour ne pas entraîner une surcharge cognitive de NERON.',
                'crew_lock_name' => 'Verrou équipage',
                'crew_lock_description' => ':point: Verrouillage Pilotage : Les non-pilotes ne sont pas autorisés à piloter un Patrouilleur/Icarus.//:point: Verrouillage Projet : les non-concepteurs ne sont pas autorisés à participer au développement des projets.',
                'plasma_shield_name' => 'Bouclier Plasma',
                'plasma_shield_description' => 'Active le bouclier plasma.',
                'magnetic_net_name' => 'Filet magnétique',
                'magnetic_net_description' => 'Active le filet magnétique qui permet d\'abandonner les Patrouilleurs à leur triste sort. Ou pas...',
                'neron_inhibition_name' => 'Entrave DMZ-CorePeace',
                'neron_inhibition_description' => 'Active l\'inhibiteur de comportements de NERON. L\'inhibiteur permet de changer un certain nombre de comportements de NERON concernant l\'agressivité.',
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
                    ['key' => 'defence', 'name' => 'Système de défense'],
                    ['key' => 'projects', 'name' => 'Projets'],
                ],
                'currentCpuPriority' => 'none',
                'crewLocks' => [
                    ['key' => 'projects', 'name' => 'Projets'],
                    ['key' => 'piloting', 'name' => 'Pilotage'],
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

        $this->thenRequirementsAreEmpty($I);
    }

    public function testWhenChunIsInLabIShouldSeeThatRequirement(FunctionalTester $I): void
    {
        $this->givenChunIsInLab();
        $terminal = $this->givenLabTerminal();
        $this->givenKuanTiIsFocusedInResearchLab($terminal);
        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);
        $requirements = $normalizedTerminal['infos']['requirements'];
        $I->assertEquals([$this->isChunPresentText], $requirements);
    }

    public function testWhenAMushIsDeadIShouldSeeThatRequirement(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();
        $this->givenAMushIsDead($I);
        $terminal = $this->givenLabTerminal();
        $this->givenKuanTiIsFocusedInResearchLab($terminal);
        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);
        $requirements = $normalizedTerminal['infos']['requirements'];
        $I->assertContains($this->isAnyMushDeadText, $requirements);
    }

    public function testShouldNormalizeItemsInLaboratoryAndInPlayerInventory(FunctionalTester $I)
    {
        $playerInventory = [
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::ITRACKIE,
                equipmentHolder: $this->kuanTi,
                reasons: [],
                time: new \DateTime()
            ),
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::FUEL_CAPSULE,
                equipmentHolder: $this->kuanTi,
                reasons: [],
                time: new \DateTime()
            ),
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::BLASTER,
                equipmentHolder: $this->kuanTi,
                reasons: [],
                time: new \DateTime()
            ),
        ];
        $labItems = [
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::FUEL_CAPSULE,
                equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
                reasons: [],
                time: new \DateTime()
            ),
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::MUSH_SAMPLE,
                equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
                reasons: [],
                time: new \DateTime()
            ),
        ];
        $terminal = $this->givenLabTerminal();
        $this->givenKuanTiHasItemsInInventory($playerInventory);
        $this->givenLabHasItems($labItems);
        $this->givenKuanTiIsFocusedInResearchLab($terminal);
        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);
        $items = $normalizedTerminal['items'];
        $I->assertEquals(
            $items,
            $this->normalizer->normalize(array_merge($playerInventory, $labItems), null, ['currentPlayer' => $this->kuanTi])
        );
    }

    public function testShouldNotNormalizeEquipmentInTheLab(FunctionalTester $I)
    {
        $playerInventory = [];
        $labItems = [
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: EquipmentEnum::CRYO_MODULE,
                equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
                reasons: [],
                time: new \DateTime()
            ),
        ];
        $terminal = $this->givenLabTerminal();
        $this->givenKuanTiHasItemsInInventory($playerInventory);
        $this->givenLabHasItems($labItems);
        $this->givenKuanTiIsFocusedInResearchLab($terminal);
        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);
        $items = $normalizedTerminal['items'];
        $I->assertEmpty($items);
    }

    public function testShouldNormalizeHiddenItemsInLab(FunctionalTester $I)
    {
        $blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
        $this->givenItemIsHidden($blaster);
        $terminal = $this->givenLabTerminal();
        $this->givenKuanTiIsFocusedInResearchLab($terminal);
        $normalizedTerminal = $this->whenINormalizeTheTerminalForKuanTi($terminal);
        $items = $normalizedTerminal['items'];
        $I->assertNotEmpty($items);
    }

    private function givenItemIsHidden($blaster)
    {
        $hideAction = new ActionConfig();
        $hideAction
            ->setActionName(ActionEnum::HIDE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $this->hideAction->loadParameters(
            actionConfig: $hideAction,
            actionProvider: $blaster,
            player: $this->chun,
            target: $blaster
        );
        $this->hideAction->execute();
        $blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        $this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->addEquipment($blaster);
    }

    private function givenKuanTiHasItemsInInventory($items)
    {
        foreach ($items as $item) {
            $this->kuanTi->addEquipment($item);
        }
    }

    private function givenLabHasItems($items)
    {
        foreach ($items as $item) {
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->addEquipment($item);
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

    private function whenINormalizeTheTerminalForKuanTi($terminal)
    {
        return $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->kuanTi]);
    }
}
