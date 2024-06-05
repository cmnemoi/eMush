<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
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
    private Project $pilgredProject;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->terminalNormalizer = $I->grabService(TerminalNormalizer::class);
        $this->terminalNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->pilgredProject = $this->daedalus->getPilgred();
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
        $this->pilgredProject->makeProgress(100);

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
            expected: [ActionEnum::EXIT_TERMINAL->value, ActionEnum::CHANGE_NERON_CPU_PRIORITY->value, ActionEnum::CHANGE_NERON_CREW_LOCK->value],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertEquals(
            expected: [
                'cpu_priority_name' => 'Priorité CPU',
                'cpu_priority_description' => 'Contrôle la répartition des tranches CPU. Soit elle rend les projets plus faciles à réaliser, soit elle accroît le nombre de GeoDonnées fournies par les analyses de planètes, soit les CPU sont en mode paresseux et ne favorisent rien du tout.//Cette propriété ne peut être changée qu\'*une fois par jour et par personne* pour ne pas entraîner une surcharge cognitive de NERON.',
                'crew_lock_name' => 'Verrou équipage',
                'crew_lock_description' => ':point: Verrouillage Pilotage : Les non-pilotes ne sont pas autorisés à piloter un Patrouilleur/Icarus.//:point: Verrouillage Projet : les non-concepteurs ne sont pas autorisés à participer au développement des projets.',
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
            ],
            actual: $normalizedTerminal['infos']
        );
    }
}
