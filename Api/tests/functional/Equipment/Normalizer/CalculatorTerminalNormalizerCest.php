<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
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
    private NeronVersionRepositoryInterface $neronVersionRepository;

    private GameEquipment $calculator;

    private array $normalizedTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->terminalNormalizer = $I->grabService(TerminalNormalizer::class);
        $this->terminalNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->neronVersionRepository = $I->grabService(NeronVersionRepositoryInterface::class);

        $this->givenCalcualtorInRoom();
        $this->givenPlayerIsFocusedOnCalculatorTerminal();

        $this->neronVersionRepository->save(new NeronVersion($this->daedalus->getId()));
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
            expected: [
                ActionEnum::EXIT_TERMINAL->toString(),
            ],
            actual: array_map(static fn ($action) => $action['key'], $this->normalizedTerminal['actions'])
        );
    }

    public function shouldNormalizeNothingToComputeInfoWithoutStarmapFragment(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: 'Rien à calculer pour le moment. // En attente de matériel dans l\'étagère...',
            actual: $this->normalizedTerminal['infos']['nothingToCompute']
        );
    }

    public function shouldNotNormalizeNothingToComputeInfoWithStarmapFragment(FunctionalTester $I): void
    {
        $this->givenAStarmapFragmentInRoom();

        $this->whenINormalizeTerminalForPlayer();

        $I->assertNull($this->normalizedTerminal['infos']['nothingToCompute']);
    }

    public function shouldNormalizeSectionTitles(FunctionalTester $I): void
    {
        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: [
                'to_a_new_eden_title' => 'Vers un nouvel Eden',
                'to_a_new_eden_description' => 'Le cristalite contient des coordonnées polaires qui se rapportent au centre du nuage de Magellan... Mais elles pointent hors de celui-ci, il va nous falloir une sacrée propulsion...',
            ],
            actual: $this->normalizedTerminal['sectionTitles']
        );
    }

    public function shouldNormalizeEdenComputedInfoIfEdenIsComputed(FunctionalTester $I): void
    {
        $this->givenEdenCoordinatesAreComputed();

        $this->whenINormalizeTerminalForPlayer();

        $I->assertEquals(
            expected: 'Le calcul des coordonnées d\'Eden a été achevé avec succès. Les coordonnées ont été transférées vers NERON : consultez votre ***Commandant*** pour plus d\'informations.',
            actual: $this->normalizedTerminal['infos']['edenComputed']
        );
    }

    public function shouldNormalizeComputeActionWithAtLeastOneStarmapFragment(FunctionalTester $I): void
    {
        $this->givenAStarmapFragmentInRoom();

        $this->whenINormalizeTerminalForPlayer();

        $I->assertContains(
            needle: ActionEnum::COMPUTE_EDEN->toString(),
            haystack: array_map(static fn ($action) => $action['key'], $this->normalizedTerminal['actions'])
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

    private function givenAStarmapFragmentInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::STARMAP_FRAGMENT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenEdenCoordinatesAreComputed(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EDEN_COMPUTED,
            holder: $this->player->getDaedalus(),
            tags: [],
            time: new \DateTime()
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
