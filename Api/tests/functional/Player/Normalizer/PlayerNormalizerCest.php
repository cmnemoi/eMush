<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Normalizer;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Normalizer\CurrentPlayerNormalizer;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PlayerNormalizerCest extends AbstractFunctionalTest
{
    private CurrentPlayerNormalizer $currentPlayerNormalizer;
    private NormalizerInterface $normalizer;

    private GameEquipmentServiceInterface $gameEquipmentService;

    private Action $dropConfig;
    private Drop $dropAction;

    private Action $takeConfig;
    private Take $takeAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->currentPlayerNormalizer = $I->grabService(CurrentPlayerNormalizer::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->normalizer = $I->grabService(NormalizerInterface::class);

        $this->dropConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::DROP]);
        $this->dropAction = $I->grabService(Drop::class);

        $this->takeConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::TAKE]);
        $this->takeAction = $I->grabService(Take::class);

        $this->currentPlayerNormalizer->setNormalizer($this->normalizer);
    }

    public function testPlayerItemsNormalization(FunctionalTester $I): void
    {
        // given I have a player with a post-it
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // given I have a drug in player's place
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // when I normalize the player
        $normalizedPlayer = $this->currentPlayerNormalizer->normalize($this->player, null, ['currentPlayer' => $this->player]);

        // then the player should be normalized with the items in this order : post-it first, drug second
        $playerNormalizedItems = $normalizedPlayer['items'];
        $I->assertEquals(ItemEnum::POST_IT, $playerNormalizedItems[0]['key']);
        $I->assertEquals(GameDrugEnum::BACTA, $playerNormalizedItems[1]['key']);
    }

    public function testPlayerItemsAreNormalizedInAStackFashionAfterAManipulation(FunctionalTester $I): void
    {
        // given I have a post-it in player's place
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // given I have a drug in player's place
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // given the player drops the post-it in the place
        $this->dropAction->loadParameters($this->dropConfig, $this->player, $postIt);
        $this->dropAction->execute();

        // given the player takes it back in their inventory
        $this->takeAction->loadParameters($this->takeConfig, $this->player, $postIt);
        $this->takeAction->execute();

        // when I normalize the player
        $normalizedPlayer = $this->currentPlayerNormalizer->normalize($this->player, null, ['currentPlayer' => $this->player]);

        // then the items should be normalized in a stack fashion : drug first, post-it second
        $playerNormalizedItems = $normalizedPlayer['items'];
        $I->assertEquals(GameDrugEnum::BACTA, $playerNormalizedItems[0]['key']);
        $I->assertEquals(ItemEnum::POST_IT, $playerNormalizedItems[1]['key']);
    }
}
