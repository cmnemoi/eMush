<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class CalculatorTerminalNormalizerCest extends AbstractFunctionalTest
{
    private TerminalNormalizer $terminalNormalizer;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $calculator;

    private array $normalizedTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->terminalNormalizer = $I->grabService(TerminalNormalizer::class);
        $this->terminalNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenCalcualtorInRoom();
        $this->givenPlayerIsFocusedOnCalculatorTerminal();
    }

    public function shouldNormalizeName(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(expected: 'Calculateur', actual: $this->normalizedTerminal['name']);
    }

    public function shouldNormalizeTips(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: "Ce module permet d'effectuer des calculs savants et/ou sans intérêt. Mais parfois la science réserve de bien belles surprises...",
            actual: $this->normalizedTerminal['tips']
        );
    }

    public function shouldNormalizeActionsWithoutStarmapFragment(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [ActionEnum::EXIT_TERMINAL->toString()],
            actual: array_map(static fn ($action) => $action['key'], $this->normalizedTerminal['actions'])
        );
    }

    public function shouldNormalizeSectionTitles(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [
                'to_a_new_eden_title' => 'Vers un nouvel Eden',
                'to_a_new_eden_description' => 'Le cristalite contient dees coordonnées polaires qui se rapportent au centre du nuage de Magellan... Mais elles pointent hors de celui-ci, il va nous falloir une sacré propulsion...',
            ],
            actual: $this->normalizedTerminal['sectionTitles']
        );
    }

    private function givenCalcualtorInRoom(): void
    {
        $this->calculator = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CALCULATOR,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsFocusedOnCalculatorTerminal(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->calculator
        );
    }

    private function whenINormalizeTerminalForPlayer(): void
    {
        $this->normalizedTerminal = $this->terminalNormalizer->normalize(
            $this->calculator,
            format: null,
            context: ['currentPlayer' => $this->player]
        );
    }
}
