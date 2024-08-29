<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ShootActionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Shoot $shootAction;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $blaster;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT]);
        $this->shootAction = $I->grabService(Shoot::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenChunHasABlaster();
    }

    public function shouldRemoveHealthPointsToTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToHit();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(8, $I);
    }

    public function armorShouldReduceDamage(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToHit();

        $this->givenBlasterInflictsOneDamage();

        $this->givenKuanTiHasPlasteniteArmor();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
    }

    public function criticalHitShouldInflictInjuryToTarget(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToHit();

        $this->givenBlasterHas100ChanceToDoCriticalHit();

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function criticalHitShouldIgnoreArmor(FunctionalTester $I): void
    {
        $this->givenBlasterHas100ChanceToHit();

        $this->givenBlasterHas100ChanceToDoCriticalHit();

        $this->givenBlasterInflictsOneDamage();

        $this->givenKuanTiHasPlasteniteArmor();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunShootsAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    public function criticalMissShouldInflictInjuryToPlayer(FunctionalTester $I): void
    {
        $this->givenBlasterHas0ChanceToHit();

        $this->givenBlasterHas100ChanceToDoCriticalMiss();

        $this->whenChunShootsAtKuanTi();

        $this->thenChunShouldHaveAnInjury($I);
    }

    public function shouldNotBeExecutableIfBlasterIsBroken(FunctionalTester $I): void
    {
        $this->givenBlasterIsBroken();

        $this->whenChunShootsAtKuanTi();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $I);
    }

    public function shouldNotBeExecutableIfBlasterIsUncharged(FunctionalTester $I): void
    {
        $this->givenBlasterIsUncharged();

        $this->whenChunShootsAtKuanTi();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::UNLOADED_WEAPON, $I);
    }

    private function givenChunHasABlaster(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBlasterHas100ChanceToHit(): void
    {
        $this->actionConfig->setSuccessRate(100);
    }

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->kuanTi->setHealthPoint($healthPoints);
    }

    private function givenBlasterHas100ChanceToDoCriticalHit(): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setCriticalSuccessRate(100);
    }

    private function givenBlasterInflictsOneDamage(): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setBaseDamageRange([1 => 1]);
    }

    private function givenKuanTiHasPlasteniteArmor(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBlasterHas0ChanceToHit(): void
    {
        $this->actionConfig->setSuccessRate(0);
    }

    private function givenBlasterHas100ChanceToDoCriticalMiss(): void
    {
        $this->blaster->getWeaponMechanicOrThrow()->setCriticalFailRate(100);
    }

    private function givenBlasterIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBlasterIsUncharged(): void
    {
        $this->statusService->updateCharge(
            chargeStatus: $this->blaster->getUsedCharge(ActionEnum::SHOOT->value),
            delta: -1,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunShootsAtKuanTi(): void
    {
        $this->shootAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->shootAction->execute();
    }

    private function thenKuanTiShouldHaveLessOrEqualHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertLessThanOrEqual($healthPoints, $this->kuanTi->getHealthPoint());
    }

    private function thenKuanTiShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->kuanTi->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury()));
    }

    private function thenKuanTiShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->kuanTi->getHealthPoint());
    }

    private function thenChunShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->chun->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury()));
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->shootAction->cannotExecuteReason());
    }
}
