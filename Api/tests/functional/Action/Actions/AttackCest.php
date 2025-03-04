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
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
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
        $this->actionConfig->setSuccessRate(100);
    }

    public function shouldRemoveHealthToTargetOnSuccess(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE->toString());

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunAttacksKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualHealthPoints(9, $I);
    }

    public function armorShouldReduceDamage(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE->toString());

        $this->givenKnifeInflictsOneDamage();

        $this->givenKuanTiHasPlasteniteArmor();

        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunAttacksKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
    }

    public function criticalHitEventShouldInflictInjuryToTarget(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString());

        $this->whenChunAttacksKuanTi();

        $this->thenKuanTiShouldHaveAnInjury($I);
    }

    public function instagibEventShouldKillTarget(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_INSTAGIB_BLED->toString());

        $this->whenChunAttacksKuanTi();

        $this->thenKuanTiShouldBeDead($I);
    }

    public function instagibEventShouldKillTargetWithCauseBledOut(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_INSTAGIB_BLED->toString());

        $this->whenChunAttacksKuanTi();

        $this->thenKuanTiShouldDieBledOut($I);
    }

    public function bruisedShoulderEventShouldInflictInjuryToPlayer(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_SHOOTER_BRUISED_SHOULDER->toString());

        $this->whenChunAttacksKuanTi();

        $this->thenChunShouldHaveAnInjury($I);
    }

    public function breakWeaponEventShouldBreakKnife(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_BREAK_WEAPON->toString());

        $this->whenChunAttacksKuanTi();

        $this->thenKnifeShouldBeBroken($I);
    }

    public function dropWeaponEventShouldDropKnife(FunctionalTester $I): void
    {
        $this->givenKnifeHas100ChanceToDispatchEvent(WeaponEventEnum::KNIFE_SHOOTER_DROP_WEAPON->toString());

        $this->whenChunAttacksKuanTi();

        $this->thenKnifeShouldBeInShelf($I);
    }

    public function shouldNotBeExecutableIfKnifeIsBroken(FunctionalTester $I): void
    {
        $this->givenKnifeIsBroken();

        $this->whenChunAttacksKuanTi();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $I);
    }

    private function givenKnifeHas100ChanceToDispatchEvent(string $event): void
    {
        $this->knife->getWeaponMechanicOrThrow()->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->knife->getWeaponMechanicOrThrow()->setFailedEventKeys([
            $event => 1,
        ]);
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

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->kuanTi->setHealthPoint($healthPoints);
    }

    private function givenKnifeInflictsOneDamage(): void
    {
        $this->knife->getWeaponMechanicOrThrow()->setDamageSpread([1, 1]);
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

    private function givenKnifeIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->knife,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunAttacksKuanTi(): void
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

    private function thenKuanTiShouldBeDead(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->isDead());
    }

    private function thenKuanTiShouldDieBledOut(FunctionalTester $I): void
    {
        $I->assertEquals($this->kuanTi->getPlayerInfo()->getClosedPlayer()->getEndCause(), EndCauseEnum::BLED);
    }

    private function thenChunShouldHaveAnInjury(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->chun->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury()));
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->attack->cannotExecuteReason());
    }

    private function thenKnifeShouldBeBroken(FunctionalTester $I): void
    {
        $I->assertTrue($this->knife->isBroken());
    }

    private function thenKnifeShouldBeInShelf(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->hasEquipmentByName(ItemEnum::KNIFE));
    }
}
