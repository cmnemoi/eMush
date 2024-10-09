<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\PetCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PetCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private PetCat $petCat;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PET_CAT->value]);
        $this->petCat = $I->grabService(PetCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasCatInInventory($I);
    }

    public function shouldNotBeVisibleIfPlayerIsGermaphobe(FunctionalTester $I): void
    {
        $this->givenPlayerIsGermaphobe();

        $this->whenPlayerTriesToPetCat();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldGiveThreeMoralePointsToPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralePoints(10);

        $this->whenPlayerPetsCat();

        $this->thenPlayerShouldHaveMoralePoints(13, $I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldNotGiveMoralePointsIfAlreadyDoneOnce(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldInfectHumanIfCatIsInfected(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldNotInfectMushPlayer(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    public function shouldPrintLogInMushChannelWhenInfectingPlayer(FunctionalTester $I): void
    {
        $I->markTestIncomplete();
    }

    private function givenPlayerHasCatInInventory(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsGermaphobe(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GERMAPHOBE,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasMoralePoints(int $moralePoints): void
    {
        $this->player->setMoralPoint($moralePoints);
    }

    private function whenPlayerTriesToPetCat(): void
    {
        $this->petCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->schrodinger,
            player: $this->player,
            target: $this->schrodinger,
        );
    }

    private function whenPlayerPetsCat(): void
    {
        $this->whenPlayerTriesToPetCat();
        $this->petCat->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->petCat->isVisible());
    }

    private function thenPlayerShouldHaveMoralePoints(int $expectedMoralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedMoralePoints, $this->player->getMoralPoint());
    }
}
