<?php

namespace Mush\Tests\functional\Modifier\Service;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Repair;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GearModifierStackingCest extends AbstractFunctionalTest
{
    private Drop $dropAction;
    private Repair $repairAction;
    private Take $takeAction;

    private ActionConfig $dropActionConfig;
    private ActionConfig $repairActionConfig;
    private ActionConfig $takeActionConfig;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $scooter;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->dropAction = $I->grabService(Drop::class);
        $this->repairAction = $I->grabService(Repair::class);
        $this->takeAction = $I->grabService(Take::class);

        $this->dropActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DROP]);
        $this->takeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TAKE]);

        $this->repairActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'repair_percent_25']);
        $this->repairActionConfig->setSuccessRate(100);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldFixedGearRetainModifierAmount(FunctionalTester $I): void
    {
        $this->givenScooterIsInTheRoom();
        $this->givenThisScooterIsBroken();
        $this->whenPlayerTakesScooter();
        $this->thenPlayerShouldHaveTheFollowingAmountOfModifiers(1, $I);
        $this->whenPlayerRepairsScooter();
        $this->thenPlayerShouldHaveAtMostTheFollowingAmountOfModifiers(1, $I);
        $this->whenPlayerDropsScooter();
        $this->thenPlayerShouldHaveTheFollowingAmountOfModifiers(0, $I);
    }

    public function shouldTwoItemsOfSameModifierStack(FunctionalTester $I): void
    {
        $this->givenPlayerHasPairOfLenses();
        $this->thenPlayerShouldHaveTheFollowingAmountOfModifiers(2, $I);
        $this->givenPlayerHasPairOfLenses();
        $this->thenPlayerShouldHaveTheFollowingAmountOfModifiers(4, $I);
    }

    private function givenScooterIsInTheRoom(): void
    {
        $this->scooter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::ANTIGRAV_SCOOTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasPairOfLenses(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::NCC_LENS,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenThisScooterIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->scooter,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerDropsScooter(): void
    {
        $this->dropAction->loadParameters($this->dropActionConfig, $this->scooter, $this->player, $this->scooter);
        $this->dropAction->execute();
    }

    private function whenPlayerTakesScooter(): void
    {
        $this->takeAction->loadParameters($this->takeActionConfig, $this->scooter, $this->player, $this->scooter);
        $this->takeAction->execute();
    }

    private function whenPlayerRepairsScooter(): void
    {
        $this->repairAction->loadParameters($this->repairActionConfig, $this->scooter, $this->player, $this->scooter);
        $this->repairAction->execute();
    }

    private function thenPlayerShouldHaveTheFollowingAmountOfModifiers(int $modifierCount, FunctionalTester $I): void
    {
        $I->assertCount($modifierCount, $this->player->getModifiers());
    }

    private function thenPlayerShouldHaveAtMostTheFollowingAmountOfModifiers(int $modifierCount, FunctionalTester $I): void
    {
        $I->assertLessOrEquals($modifierCount, $this->player->getModifiers()->count());
    }
}
