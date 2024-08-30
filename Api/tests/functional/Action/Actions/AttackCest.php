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
final class AttackCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Attack $attack;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $knife;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ATTACK]);
        $this->attack = $I->grabService(Attack::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenChunHasAKnife();
    }

    public function shouldRemoveHealthPointsToTarget(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToHit();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunAttacksAtKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(9, $I);
    }

    public function armorShouldReduceDamage(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToHit();

        $this->givenKnifeHas0ChanceToDoCriticalHit();

        $this->givenKnifeInflictsOneDamage();

        $this->givenKuanTiHasPlasteniteArmor();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunAttacksAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
    }

    public function criticalHitShouldInflictInjuryToTarget(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToHit();

        $this->givenKnifeHas100ChanceToDoCriticalHit();

        $this->whenChunAttacksAtKuanTi();

        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function criticalHitShouldIgnoreArmor(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToHit();

        $this->givenKnifeHas100ChanceToDoCriticalHit();

        $this->givenKnifeInflictsOneDamage();

        $this->givenKuanTiHasPlasteniteArmor();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunAttacksAtKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    public function criticalMissShouldInflictInjuryToPlayer(FunctionalTester $I): void
    {
        $this->givenKnifeHas0ChanceToHit();

        $this->givenKnifeHas100ChanceToDoCriticalMiss();

        $this->whenChunAttacksAtKuanTi();

        $this->thenChunShouldHaveAnInjury($I);
    }

    public function shouldNotBeExecutableIfKnifeIsBroken(FunctionalTester $I): void
    {
        $this->givenKnifeIsBroken();

        $this->whenChunAttacksAtKuanTi();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $I);
    }

    private function givenChunHasAKnife(): void
    {
        $this->knife = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::KNIFE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKnifeHas100ChanceToHit(): void
    {
        $this->actionConfig->setSuccessRate(100);
    }

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->kuanTi->setHealthPoint($healthPoints);
    }

    private function givenKnifeHas100ChanceToDoCriticalHit(): void
    {
        $this->knife->getWeaponMechanicOrThrow()->setCriticalSuccessRate(100);
    }

    private function givenKnifeHas0ChanceToDoCriticalHit(): void
    {
        $this->knife->getWeaponMechanicOrThrow()->setCriticalSuccessRate(0);
    }

    private function givenKnifeInflictsOneDamage(): void
    {
        $this->knife->getWeaponMechanicOrThrow()->setBaseDamageRange([1 => 1]);
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

    private function givenKnifeHas0ChanceToHit(): void
    {
        $this->actionConfig->setSuccessRate(0);
    }

    private function givenKnifeHas100ChanceToDoCriticalMiss(): void
    {
        $this->knife->getWeaponMechanicOrThrow()->setCriticalFailRate(100);
    }

    private function givenKnifeIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->knife,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunAttacksAtKuanTi(): void
    {
        $this->attack->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->knife,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->attack->execute();
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
        $I->assertEquals($message, $this->attack->cannotExecuteReason());
    }
}
