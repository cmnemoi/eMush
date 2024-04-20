<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Equipment\Enum\EquipmentEnum;
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
        $pilgredReflection = new \ReflectionClass($this->pilgredProject);
        $pilgredReflection->getProperty('progress')->setValue($this->pilgredProject, 100);

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

        $I->assertEquals(expected: 'Le PILGRED est pleinement opérationnel.', actual: $normalizedTerminal['infos']['pilgredFinishedDescription']);
    }
}
