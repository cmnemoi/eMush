<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HitActionCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private ActionConfig $action;
    private Weapon $bareHandMechanic;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private AddSkillToPlayerService $addSkillToPlayer;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HIT]);
        $this->action->setDirtyRate(0);

        $this->hitAction = $I->grabService(Hit::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);

        $this->bareHandMechanic = $I->grabEntityFromRepository(Weapon::class, ['name' => EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::BARE_HANDS . '_' . GameConfigEnum::DEFAULT]);
        $this->givenHitDamageIs([1, 1]);
    }

    public function testHitSuccess(FunctionalTester $I)
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());
        $this->action->setSuccessRate(101);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );

        $this->hitAction->execute();

        $I->assertNotEquals($this->player2->getHealthPoint(), $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }

    public function testHitFail(FunctionalTester $I)
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_FAILED_HIT->toString());
        $this->action->setSuccessRate(0);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );

        $this->hitAction->execute();

        $I->assertEquals($this->player2->getHealthPoint(), $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }

    public function testHitArmor(FunctionalTester $I)
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());
        $this->action->setSuccessRate(100);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );

        /** @var VariableEventModifierConfig $armorModifierConfig */
        $armorModifierConfig = $I->grabEntityFromRepository(
            VariableEventModifierConfig::class,
            ['name' => 'modifier_for_target_player_+1healthPoint_on_injury']
        );
        $armorModifierConfig->setDelta(5);
        $modifier = new GameModifier($this->player2, $armorModifierConfig);
        $modifier->setModifierProvider($this->player2);

        $I->haveInRepository($modifier);
        $I->refreshEntities($this->player2);

        $this->hitAction->execute();

        // should not lose health point because of armor
        $I->assertEquals(
            $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player2->getHealthPoint(),
        );
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost(),
            $this->player1->getActionPoint(),
        );
    }

    public function testHitCriticalSuccess(FunctionalTester $I)
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_TARGET_BURST_NOSE_TARGET_10_PERCENTS->toString());
        $this->action->setSuccessRate(100);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );

        /** @var VariableEventModifierConfig $armorModifierConfig */
        $armorModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'modifier_for_target_player_+1healthPoint_on_injury',
        ]);
        $armorModifierConfig->setDelta(-3);
        $modifier = new GameModifier($this->player2, $armorModifierConfig);

        $I->haveInRepository($modifier);
        $I->refreshEntities($this->player2);

        $this->whenChunHitsKuanTi();

        // critical hits should bypass armor
        $I->assertNotEquals(
            $this->player2->getHealthPoint(),
            $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint()
        );
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }

    public function shouldHaveABonusAgainstInactivePlayers(FunctionalTester $I): void
    {
        $this->givenChunHasInactiveStatus();

        $this->givenHitActionHasSuccessRate(60);

        $this->whenKuanTiTriesToHitChun();

        $this->thenHitActionSuccessRateShouldBe(90, $I);
    }

    public function shouldHaveABonusForHighlyInactivePlayers(FunctionalTester $I): void
    {
        $this->givenChunHasHighlyInactiveStatus();

        $this->givenHitActionHasSuccessRate(60);

        $this->whenKuanTiTriesToHitChun();

        $this->thenHitActionSuccessRateShouldBe(90, $I);
    }

    public function sneakPlayerShouldBeHarderToHit(FunctionalTester $I): void
    {
        $this->givenHitActionHasSuccessRate(60);

        $this->givenChunHasSneakSkill();

        $this->whenKuanTiTriesToHitChun();

        $this->thenHitActionSuccessRateShouldBe(45, $I);
    }

    public function solidPlayerShouldDoMoreDamage(FunctionalTester $I): void
    {
        $this->givenHitActionHasSuccessRate(100);

        $this->givenKuanTiHasHealthPoint(10);

        $this->givenChunHasSolidSkill();

        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());

        $this->whenChunHitsKuanTi();

        $this->thenKuanTiShouldHaveExactlyHealthPoint(8, $I);
    }

    public function wrestlerPlayerShouldDoMoreDamage(FunctionalTester $I): void
    {
        $this->givenHitActionHasSuccessRate(100);

        $this->givenKuanTiHasHealthPoint(10);

        $this->givenChunHasWrestlerSkill();

        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());

        $this->whenChunHitsKuanTi();

        $this->thenKuanTiShouldHaveExactlyHealthPoint(7, $I);
    }

    public function shouldNotBeVisibleIfPlayerHasAKnife(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAKnife();

        $this->whenKuanTiTriesToHitChun();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldDealLessDamageOnHardBoiledPlayer(FunctionalTester $I): void
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());

        $this->givenHitDamageIs([2, 2]);

        $this->givenHitActionHasSuccessRate(100);

        $this->givenKuanTiHasHealthPoint(10);

        $this->addSkillToPlayer->execute(SkillEnum::HARD_BOILED, $this->kuanTi);

        $this->whenChunHitsKuanTi();

        $this->thenKuanTiShouldHaveMoreOrEqualThanHealthPoint(9, $I);
    }

    public function ninjaLogShouldBeAnonymous(FunctionalTester $I): void
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());

        $this->givenHitActionHasSuccessRate(100);

        $this->givenChunIsANinja($I);

        $this->whenChunHitsKuanTi();

        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'log' => WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT,
            ]
        );

        $character = $roomLog->getParameters()['character'];

        $I->assertEquals($character, CharacterEnum::SOMEONE);
    }

    public function armorsShouldNotHealPlayer(FunctionalTester $I): void
    {
        $this->givenBareHandsHas100ChanceToDispatchEvent(WeaponEventEnum::BARE_HANDS_SUCCESSFUL_HIT->toString());

        $this->givenHitDamageIs([0, 0]);

        $this->givenHitActionHasSuccessRate(100);

        $this->givenKuanTiHasHealthPoint(10);

        $this->givenKuanTiHasPlasteniteArmor();

        $this->givenKuanTiIsHardBoiled($I);

        $this->whenChunHitsKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualThanHealthPoint(10, $I);
    }

    private function givenChunHasInactiveStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenChunHasHighlyInactiveStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenChunHasSneakSkill(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::SNEAK, $this->chun);
    }

    private function givenHitActionHasSuccessRate(int $successRate): void
    {
        $this->action->setSuccessRate($successRate);
    }

    private function givenChunHasSolidSkill(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::SOLID, $this->chun);
    }

    private function givenChunHasWrestlerSkill(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::WRESTLER, $this->chun);
    }

    private function givenKuanTiHasAKnife(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::KNIFE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenKuanTiHasHealthPoint(int $healthPoint): void
    {
        $this->kuanTi->setHealthPoint($healthPoint);
    }

    private function givenHitDamageIs(array $damage): void
    {
        $this->bareHandMechanic->setDamageSpread($damage);
    }

    private function givenChunIsANinja(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::NINJA, $I);
    }

    private function givenKuanTiIsHardBoiled(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::HARD_BOILED, $I, $this->kuanTi);
    }

    private function givenKuanTiHasPlasteniteArmor(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenBareHandsHas100ChanceToDispatchEvent(string $event): void
    {
        $this->bareHandMechanic->setSuccessfulEventKeys([
            $event => 1,
        ]);
        $this->bareHandMechanic->setFailedEventKeys([
            $event => 1,
        ]);
    }

    private function whenKuanTiTriesToHitChun(): void
    {
        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
    }

    private function whenChunHitsKuanTi(): void
    {
        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->hitAction->execute();
    }

    private function thenHitActionSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->hitAction->getSuccessRate());
    }

    private function thenKuanTiShouldHaveLessOrEqualThanHealthPoint(int $expectedHealthPoint, FunctionalTester $I): void
    {
        $I->assertLessThanOrEqual($expectedHealthPoint, $this->kuanTi->getHealthPoint());
    }

    private function thenKuanTiShouldHaveExactlyHealthPoint(int $expectedHealthPoint, FunctionalTester $I): void
    {
        $I->assertEquals($expectedHealthPoint, $this->kuanTi->getHealthPoint());
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->hitAction->isVisible());
    }

    private function thenKuanTiShouldHaveMoreOrEqualThanHealthPoint(int $expectedHealthPoint, FunctionalTester $I): void
    {
        $I->assertGreaterThanOrEqual($expectedHealthPoint, $this->kuanTi->getHealthPoint());
    }
}
