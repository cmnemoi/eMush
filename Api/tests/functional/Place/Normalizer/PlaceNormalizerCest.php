<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Place\Normalizer;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Normalizer\PlaceNormalizer;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PlaceNormalizerCest extends AbstractFunctionalTest
{
    private PlaceNormalizer $placeNormalizer;
    private NormalizerInterface $normalizer;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private array $normalizedPlace;
    private GameItem $postIt;

    /** @var GameItem[] */
    private array $scrap;
    private GameItem $contaminatedRation;
    private GameItem $cleanRation;
    private GameItem $hiddenRation;
    private GameItem $hiddenAndFrozenRation;

    private ActionConfig $dropConfig;
    private Drop $dropAction;

    private ActionConfig $takeConfig;
    private Take $takeAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->placeNormalizer = $I->grabService(PlaceNormalizer::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->normalizer = $I->grabService(NormalizerInterface::class);

        $this->dropConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DROP]);
        $this->dropAction = $I->grabService(Drop::class);

        $this->takeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->takeAction = $I->grabService(Take::class);

        $this->placeNormalizer->setNormalizer($this->normalizer);
    }

    public function testPlaceItemsNormalization(FunctionalTester $I): void
    {
        $this->givenPlaceHasPostIt();
        $this->givenPlaceHasBacta();

        $this->whenPlaceIsNormalized();

        $this->thenPlaceItemsAreNormalizedInOrder($I);
    }

    public function testPlaceItemsAreNormalizedInAStackFashionAfterAManipulation(FunctionalTester $I): void
    {
        $this->givenPlaceHasPostIt();
        $this->givenPlaceHasBacta();
        $this->givenPlayerTakesPostIt();
        $this->givenPlayerDropsPostItBack();

        $this->whenPlaceIsNormalized();

        $this->thenItemsAreNormalizedInStackFashion($I);
    }

    public function testPlaceItemsWithPilesAreNormalizedInTheRightOrder(FunctionalTester $I): void
    {
        $this->givenPlaceHasMultipleScrapPieces();
        $this->givenPlaceHasBacta();
        $this->givenPlayerTakesOneScrapPiece();
        $this->givenPlayerDropsScrapPieceBack();

        $this->whenPlaceIsNormalized();

        $this->thenScrapPileStaysAtBeginning($I);
    }

    public function shouldPutContaminatedFoodOnPileTop(FunctionalTester $I): void
    {
        $this->givenPlaceHasCleanRations();
        $this->givenPlaceHasContaminatedRation();

        $this->whenPlaceIsNormalized();

        $this->thenContaminatedRationIsAtTopOfPile($I);
    }

    public function shouldSplitItemsIntoSeparatePilesWhenTheyHaveMultipleStatuses(FunctionalTester $I): void
    {
        $this->givenPlaceHasCleanRation();
        $this->givenPlaceHasHiddenRation();
        $this->givenPlaceHasHiddenAndFrozenRation();

        $this->whenPlaceIsNormalized();

        $this->thenItemsAreSplitIntoSeparatePiles($I);
    }

    public function shouldNormalizeMultipleSofasAsEquipmentsAndItems(FunctionalTester $I): void
    {
        // given I have a sofa in player's place
        $sofa = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $secondSofa = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when I normalize the place
        $place = $this->player->getPlace();
        $normalizedPlace = $this->placeNormalizer->normalize($place, null, ['currentPlayer' => $this->player]);

        // then the sofas should be normalized as equipments and items
        $placeNormalizedEquipments = $normalizedPlace['equipments'];
        $placeNormalizedItems = $normalizedPlace['items'];
        $I->assertEquals(EquipmentEnum::SWEDISH_SOFA, $placeNormalizedEquipments[0]['key']);
        $I->assertEquals(EquipmentEnum::SWEDISH_SOFA, $placeNormalizedEquipments[1]['key']);
        $I->assertEquals(EquipmentEnum::SWEDISH_SOFA, $placeNormalizedItems[0]['key']);
        $I->assertEquals(EquipmentEnum::SWEDISH_SOFA, $placeNormalizedItems[1]['key']);
    }

    private function givenPlaceHasPostIt(): void
    {
        $this->postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlaceHasBacta(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenPlaceIsNormalized(): void
    {
        $this->normalizedPlace = $this->placeNormalizer->normalize(
            $this->player->getPlace(),
            null,
            ['currentPlayer' => $this->player]
        );
    }

    private function thenPlaceItemsAreNormalizedInOrder(FunctionalTester $I): void
    {
        $placeNormalizedItems = $this->normalizedPlace['items'];
        $I->assertEquals(ItemEnum::POST_IT, $placeNormalizedItems[0]['key']);
        $I->assertEquals(GameDrugEnum::BACTA, $placeNormalizedItems[1]['key']);
    }

    private function givenPlayerTakesPostIt(): void
    {
        $this->takeAction->loadParameters($this->takeConfig, $this->postIt, $this->player, $this->postIt);
        $this->takeAction->execute();
    }

    private function givenPlayerDropsPostItBack(): void
    {
        $this->dropAction->loadParameters($this->dropConfig, $this->postIt, $this->player, $this->postIt);
        $this->dropAction->execute();
    }

    private function thenItemsAreNormalizedInStackFashion(FunctionalTester $I): void
    {
        $placeNormalizedItems = $this->normalizedPlace['items'];
        $I->assertEquals(GameDrugEnum::BACTA, $placeNormalizedItems[0]['key']);
        $I->assertEquals(ItemEnum::POST_IT, $placeNormalizedItems[1]['key']);
    }

    private function givenPlaceHasMultipleScrapPieces(): void
    {
        $this->scrap = [];
        foreach (range(1, 3) as $i) {
            $this->scrap[] = $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::METAL_SCRAPS,
                equipmentHolder: $this->player->getPlace(),
                reasons: [],
                time: new \DateTime()
            );
        }
    }

    private function givenPlayerTakesOneScrapPiece(): void
    {
        $this->takeAction->loadParameters($this->takeConfig, $this->scrap[2], $this->player, $this->scrap[2]);
        $this->takeAction->execute();
    }

    private function givenPlayerDropsScrapPieceBack(): void
    {
        $this->dropAction->loadParameters($this->dropConfig, $this->scrap[2], $this->player, $this->scrap[2]);
        $this->dropAction->execute();
    }

    private function thenScrapPileStaysAtBeginning(FunctionalTester $I): void
    {
        $placeNormalizedItems = $this->normalizedPlace['items'];
        $I->assertEquals(ItemEnum::METAL_SCRAPS, $placeNormalizedItems[0]['key']);
        $I->assertEquals(GameDrugEnum::BACTA, $placeNormalizedItems[1]['key']);
    }

    private function givenPlaceHasCleanRations(): void
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GameRationEnum::COOKED_RATION,
                equipmentHolder: $this->player->getPlace(),
                reasons: [],
                time: new \DateTime()
            );
        }
    }

    private function givenPlaceHasContaminatedRation(): void
    {
        $this->contaminatedRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COOKED_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::CONTAMINATED,
            holder: $this->contaminatedRation,
            target: $this->player,
        );
    }

    private function thenContaminatedRationIsAtTopOfPile(FunctionalTester $I): void
    {
        $placeNormalizedItems = $this->normalizedPlace['items'];
        $I->assertEquals($this->contaminatedRation->getId(), $placeNormalizedItems[0]['id']);
    }

    private function givenPlaceHasCleanRation(): void
    {
        $this->cleanRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COOKED_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlaceHasHiddenRation(): void
    {
        $this->hiddenRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COOKED_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::HIDDEN,
            $this->hiddenRation,
            [],
            new \DateTime(),
            $this->player
        );
    }

    private function givenPlaceHasHiddenAndFrozenRation(): void
    {
        $this->hiddenAndFrozenRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COOKED_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::HIDDEN,
            $this->hiddenAndFrozenRation,
            [],
            new \DateTime(),
            $this->player
        );
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::FROZEN,
            $this->hiddenAndFrozenRation,
            [],
            new \DateTime()
        );
    }

    private function thenItemsAreSplitIntoSeparatePiles(FunctionalTester $I): void
    {
        $placeNormalizedItems = $this->normalizedPlace['items'];

        $I->assertCount(
            3,
            $placeNormalizedItems,
            'Items should be split into 3 different piles based on their status combinations'
        );

        // Find each pile by checking their item IDs
        $cleanPileFound = false;
        $hiddenPileFound = false;
        $hiddenFrozenPileFound = false;

        foreach ($placeNormalizedItems as $pile) {
            if ($pile['id'] === $this->cleanRation->getId()) {
                $cleanPileFound = true;
            } elseif ($pile['id'] === $this->hiddenRation->getId()) {
                $hiddenPileFound = true;
            } elseif ($pile['id'] === $this->hiddenAndFrozenRation->getId()) {
                $hiddenFrozenPileFound = true;
            }
        }

        $I->assertTrue($cleanPileFound, 'Clean ration pile should be found');
        $I->assertTrue($hiddenPileFound, 'Hidden ration pile should be found');
        $I->assertTrue($hiddenFrozenPileFound, 'Hidden+frozen ration pile should be found');
    }
}
