<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Place\Normalizer;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
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
        // given I have a place with a post-it
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given I have a drug in player's place
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when I normalize the place
        $place = $this->player->getPlace();
        $normalizedPlace = $this->placeNormalizer->normalize($place, null, ['currentPlayer' => $this->player]);

        // then the place should be normalized with the items in this order : post-it first, drug second
        $placeNormalizedItems = $normalizedPlace['items'];
        $I->assertEquals(ItemEnum::POST_IT, $placeNormalizedItems[0]['key']);
        $I->assertEquals(GameDrugEnum::BACTA, $placeNormalizedItems[1]['key']);
    }

    public function testPlaceItemsAreNormalizedInAStackFashionAfterAManipulation(FunctionalTester $I): void
    {
        // given I have a post-it in player's place
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given I have a drug in player's place
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given the player takes the post-it
        $this->takeAction->loadParameters($this->takeConfig, $postIt, $this->player, $postIt);
        $this->takeAction->execute();

        // given the player drops it back in the place
        $this->dropAction->loadParameters($this->dropConfig, $postIt, $this->player, $postIt);
        $this->dropAction->execute();

        // when I normalize the place
        $place = $this->player->getPlace();
        $normalizedPlace = $this->placeNormalizer->normalize($place, null, ['currentPlayer' => $this->player]);

        // then the items should be normalized in a stack fashion : drug first, post-it second
        $placeNormalizedItems = $normalizedPlace['items'];
        $I->assertEquals(GameDrugEnum::BACTA, $placeNormalizedItems[0]['key']);
        $I->assertEquals(ItemEnum::POST_IT, $placeNormalizedItems[1]['key']);
    }

    public function testPlaceItemsWithPilesAreNormalizedInTheRightOrder(FunctionalTester $I): void
    {
        // given I have multiple pieces of scrap in player's place so that they form a pile
        $scrap = [];
        foreach (range(1, 3) as $i) {
            $scrap[] = $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::METAL_SCRAPS,
                equipmentHolder: $this->player->getPlace(),
                reasons: [],
                time: new \DateTime()
            );
        }

        // given I have a drug in player's place
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given the player takes one piece of scrap
        $this->takeAction->loadParameters($this->takeConfig, $scrap[2], $this->player, $scrap[2]);
        $this->takeAction->execute();

        // given the player drops it back in the place
        $this->dropAction->loadParameters($this->dropConfig, $scrap[2], $this->player, $scrap[2]);
        $this->dropAction->execute();

        // when I normalize the place
        $place = $this->player->getPlace();
        $normalizedPlace = $this->placeNormalizer->normalize($place, null, ['currentPlayer' => $this->player]);

        // then the scrap pile stays at the beginning of the items list
        $placeNormalizedItems = $normalizedPlace['items'];
        $I->assertEquals(ItemEnum::METAL_SCRAPS, $placeNormalizedItems[0]['key']);
        $I->assertEquals(GameDrugEnum::BACTA, $placeNormalizedItems[1]['key']);
    }

    public function shouldPutContaminatedFoodOnPileTop(FunctionalTester $I): void
    {
        // given i have a clean rations in player's place
        $cleanRations = [];
        for ($i = 0; $i < 2; ++$i) {
            $cleanRations[] = $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GameRationEnum::COOKED_RATION,
                equipmentHolder: $this->player->getPlace(),
                reasons: [],
                time: new \DateTime()
            );
        }

        // given i have a contaminated ration in player's place
        $contaminatedRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::COOKED_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::CONTAMINATED,
            holder: $contaminatedRation,
            target: $this->player,
        );

        // when I normalize the place
        $place = $this->player->getPlace();
        $normalizedPlace = $this->placeNormalizer->normalize($place, null, ['currentPlayer' => $this->player]);

        // then the contaminated ration should be at the top of the normalized pile
        $placeNormalizedItems = $normalizedPlace['items'];
        $I->assertEquals($contaminatedRation->getId(), $placeNormalizedItems[0]['id']);
    }
}
