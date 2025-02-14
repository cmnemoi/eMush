<?php

namespace Mush\Tests\functional\Status\Listener;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EquipmentSubscriberCest extends AbstractFunctionalTest
{
    private Drop $dropAction;
    private Take $takeAction;

    private ActionConfig $dropActionConfig;
    private ActionConfig $takeActionConfig;

    private GameEquipmentServiceInterface $gameEquipmentService;

    private GameItem $superfreezer;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->dropAction = $I->grabService(Drop::class);
        $this->takeAction = $I->grabService(Take::class);

        $this->dropActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DROP]);
        $this->takeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TAKE]);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldSuperfreezerBeHeavy(FunctionalTester $I): void
    {
        $this->givenPlayerHasSuperfreezer();
        $this->thenPlayerShouldBeBurdened($I);
    }

    public function shouldMicrowaveBeHeavy(FunctionalTester $I): void
    {
        $this->givenPlayerHasMicrowave();
        $this->thenPlayerShouldBeBurdened($I);
    }

    public function shouldOldFaithfulBeHeavy(FunctionalTester $I): void
    {
        $this->givenPlayerHasOldFaithful();
        $this->thenPlayerShouldBeBurdened($I);
    }

    public function shouldKeepBurdenedStatusAfterDroppingSuperfreezerWhenCarryingTwoOtherHeavyItems(FunctionalTester $I): void
    {
        $this->givenPlayerHasSuperfreezer();
        $this->givenPlayerHasMicrowave();
        $this->givenPlayerHasOldFaithful();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(3, $I);
        $this->whenPlayerDropsSuperfreezer();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(2, $I);
    }

    public function shouldKeepBurdenedStatusAfterDroppingSuperfreezerWhenCarryingAnotherHeavyItem(FunctionalTester $I): void
    {
        $this->givenPlayerHasSuperfreezer();
        $this->givenPlayerHasMicrowave();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(2, $I);
        $this->whenPlayerDropsSuperfreezer();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(1, $I);
    }

    public function shouldLoseBurdenedStatusAfterDroppingSuperfreezerWhenCarryingNoOtherItems(FunctionalTester $I): void
    {
        $this->givenPlayerHasSuperfreezer();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(1, $I);
        $this->whenPlayerDropsSuperfreezer();
        $this->thenPlayerShouldNotBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(0, $I);
    }

    public function shouldGainBurdenedStatusAfterTakingSuperfreezerWhileNotCarryingAnyOtherItems(FunctionalTester $I): void
    {
        $this->givenSuperfreezerIsInPlayerRoom();
        $this->thenPlayerShouldNotBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(0, $I);
        $this->whenPlayerTakesSuperfreezer();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(1, $I);
    }

    public function shouldKeepBurdenedStatusAfterTakingSuperfreezerWhileCarryingMicrowave(FunctionalTester $I): void
    {
        $this->givenSuperfreezerIsInPlayerRoom();
        $this->givenPlayerHasMicrowave();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(1, $I);
        $this->whenPlayerTakesSuperfreezer();
        $this->thenPlayerShouldBeBurdened($I);
        $this->thenPlayerShouldCarryTheFollowingAmountOfItems(2, $I);
    }

    private function givenPlayerHasSuperfreezer(): void
    {
        $this->superfreezer = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::SUPERFREEZER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasMicrowave(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::MICROWAVE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasOldFaithful(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OLD_FAITHFUL,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenSuperfreezerIsInPlayerRoom(): void
    {
        $this->superfreezer = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::SUPERFREEZER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerDropsSuperfreezer(): void
    {
        $this->dropAction->loadParameters($this->dropActionConfig, $this->superfreezer, $this->player, $this->superfreezer);
        $this->dropAction->execute();
    }

    private function whenPlayerTakesSuperfreezer(): void
    {
        $this->takeAction->loadParameters($this->takeActionConfig, $this->superfreezer, $this->player, $this->superfreezer);
        $this->takeAction->execute();
    }

    private function thenPlayerShouldBeBurdened(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::BURDENED));
    }

    private function thenPlayerShouldNotBeBurdened(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::BURDENED));
    }

    private function thenPlayerShouldCarryTheFollowingAmountOfItems(int $itemCount, FunctionalTester $I): void
    {
        $I->assertCount($itemCount, $this->player->getEquipments());
    }
}
