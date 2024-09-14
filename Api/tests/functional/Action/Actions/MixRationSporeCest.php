<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\MixRationSpore;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MixRationSporeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private MixRationSpore $mixRationSpore;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $ration;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MIX_RATION_SPORE]);
        $this->mixRationSpore = $I->grabService(MixRationSpore::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->ration = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveFungalKitchenSkill(FunctionalTester $I): void
    {
        $this->givenKuanTiHasASpore();

        $this->whenKuanTiMixesRationWithSpore();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldCreateContaminatedStatusForRation(FunctionalTester $I): void
    {
        $this->givenKuanTiHasASpore();

        $this->givenKuanTiHasFungalKitchenSkill($I);

        $this->whenKuanTiMixesRationWithSpore();

        $this->thenRationShouldHaveContaminatedStatus($I);
    }

    public function shouldIncrementRationContaminationLevel(FunctionalTester $I): void
    {
        $this->givenKuanTiHasASpore();

        $this->givenKuanTiHasFungalKitchenSkill($I);

        $this->givenRationHasContaminatedStatus();

        $this->whenKuanTiMixesRationWithSpore();

        $this->thenRationShouldHaveContaminatedStatusWithCharge(2, $I);
    }

    public function shouldRemovePlayerSpore(FunctionalTester $I): void
    {
        $this->givenKuanTiHasASpore();

        $this->givenKuanTiHasFungalKitchenSkill($I);

        $this->whenKuanTiMixesRationWithSpore();

        $this->thenKuanTiShouldHaveSpore(0, $I);
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveASpore(FunctionalTester $I): void
    {
        $this->givenKuanTiHasFungalKitchenSkill($I);

        $this->whenKuanTiMixesRationWithSpore();

        $this->thenActionShouldNotBeVisible($I);
    }

    private function givenKuanTiHasFungalKitchenSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::FUNGAL_KITCHEN, $I, $this->kuanTi);
    }

    private function givenKuanTiHasASpore(): void
    {
        $this->kuanTi->setSpores(1);
    }

    private function whenKuanTiMixesRationWithSpore(): void
    {
        $this->mixRationSpore->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->ration,
            player: $this->kuanTi,
            target: $this->ration
        );
        $this->mixRationSpore->execute();
    }

    private function givenRationHasContaminatedStatus(): void
    {
        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::CONTAMINATED,
            holder: $this->ration,
            target: $this->kuanTi,
            time: new \DateTime(),
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->mixRationSpore->isVisible());
    }

    private function thenRationShouldHaveContaminatedStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->ration->hasStatus(EquipmentStatusEnum::CONTAMINATED));
    }

    private function thenRationShouldHaveContaminatedStatusWithCharge(int $charge, FunctionalTester $I): void
    {
        $contaminatedStatus = $this->ration->getChargeStatusByNameOrThrow(EquipmentStatusEnum::CONTAMINATED);

        $I->assertEquals($charge, $contaminatedStatus->getCharge());
    }

    private function thenKuanTiShouldHaveSpore(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->kuanTi->getSpores());
    }
}
