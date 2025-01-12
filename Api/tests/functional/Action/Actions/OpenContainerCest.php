<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\OpenContainer;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class OpenContainerCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private OpenContainer $openContainer;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $container;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'open_container_cost_0']);
        $this->openContainer = $I->grabService(OpenContainer::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function coffeeThermosShouldGiveCoffee(FunctionalTester $I): void
    {
        $this->givenThermosInShelf();
        $this->whenPlayerOpensThermos();
        $this->thenCoffeeShouldBeInPlayerInventory($I);
    }

    public function coffeeThermosShouldConsumeCharge(FunctionalTester $I): void
    {
        $this->givenThermosInShelf();
        $this->givenThermosHasCharges(4);
        $this->whenPlayerOpensThermos();
        $this->thenThermosShouldHaveCharges(3, $I);
    }

    public function coffeeThermosShouldBeDestroyedWhenEmpty(FunctionalTester $I): void
    {
        $this->givenThermosInShelf();
        $this->givenThermosHasCharges(1);
        $this->whenPlayerOpensThermos();
        $this->thenThermosShouldHaveBeenDestroyed($I);
    }

    public function anniversaryGiftShouldGiveChunOnlyChunGifts(FunctionalTester $I): void
    {
        $this->givenAnniversaryGiftInChunInventory();
        $this->whenChunOpensGift();
        $this->thenAnyOfChunGiftShouldBeInInventory($I);
    }

    private function givenThermosInShelf(): void
    {
        $this->container = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::COFFEE_THERMOS,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenThermosHasCharges(int $charges): void
    {
        $this->container->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge($charges);
    }

    private function givenAnniversaryGiftInChunInventory(): void
    {
        $this->container = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ANNIVERSARY_GIFT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenPlayerTriesToOpenThermos(): void
    {
        $this->openContainer->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->container,
            player: $this->chun,
            target: $this->container,
        );
    }

    private function whenPlayerOpensThermos(): void
    {
        $this->whenPlayerTriesToOpenThermos();
        $this->openContainer->execute();
    }

    private function whenChunTriesToOpenGift(): void
    {
        $this->openContainer->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->container,
            player: $this->chun,
            target: $this->container,
        );
    }

    private function whenChunOpensGift(): void
    {
        $this->whenChunTriesToOpenGift();
        $this->openContainer->execute();
    }

    private function thenCoffeeShouldBeInPlayerInventory(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasEquipmentByName(GameRationEnum::COFFEE));
    }

    private function thenThermosShouldHaveCharges(int $charges, FunctionalTester $I): void
    {
        $I->assertEquals($charges, $this->container->getChargeStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge());
    }

    private function thenThermosShouldHaveBeenDestroyed(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->doesNotHaveEquipmentByName(ItemEnum::COFFEE_THERMOS));
    }

    private function thenAnyOfChunGiftShouldBeInInventory(FunctionalTester $I): void
    {
        $chunEquipment = $this->chun->getEquipments()->map(static fn (GameItem $item) => $item->getEquipment()->getEquipmentName());

        $I->assertTrue(
            condition: array_intersect(['apprentron_medic', ItemEnum::MUSH_SAMPLE, ItemEnum::MYCO_ALARM, 'apprentron_optimist'], $chunEquipment->toArray()) !== [],
            message: "Chun should have a piece of equipment between Medic mage book, Mush sample, Mycoalarm or Optimist magebook, but she has: {implode(', ', {$chunEquipment})}"
        );
    }
}
