<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
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
    }

    public function testHitSuccess(FunctionalTester $I)
    {
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
        $this->action->setSuccessRate(100);
        $this->action->setCriticalRate(0);
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
            ['name' => 'armorReduceDamage']
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
        $this->action->setSuccessRate(101);
        $this->action->setCriticalRate(101);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );

        /** @var VariableEventModifierConfig $armorModifierConfig */
        $armorModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'armorReduceDamage',
        ]);
        $armorModifierConfig->setDelta(-3);
        $modifier = new GameModifier($this->player2, $armorModifierConfig);

        $I->haveInRepository($modifier);
        $I->refreshEntities($this->player2);

        $result = $this->hitAction->execute();

        $I->assertTrue($result instanceof CriticalSuccess);

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

    public function sneakPlayerShouldBeLessHarderToHit(FunctionalTester $I): void
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

        $this->whenChunHitsKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualThanHealthPoint(8, $I);
    }

    public function wrestlerPlayerShouldDoMoreDamage(FunctionalTester $I): void
    {
        $this->givenHitActionHasSuccessRate(100);

        $this->givenKuanTiHasHealthPoint(10);

        $this->givenChunHasWrestlerSkill();

        $this->whenChunHitsKuanTi();

        $this->thenKuanTiShouldHaveLessOrEqualThanHealthPoint(7, $I);
    }

    public function shouldNotBeVisibleIfPlayerHasAKnife(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAKnife();

        $this->whenKuanTiTriesToHitChun();

        $this->thenActionShouldNotBeVisible($I);
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

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->hitAction->isVisible());
    }
}
