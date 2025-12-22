<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Shower;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ShowerActionCest extends AbstractFunctionalTest
{
    private Shower $showerAction;
    private ActionConfig $action;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $shower;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->showerAction = $I->grabService(Shower::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE_SHOWER]);
        $this->action->setInjuryRate(0);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function testShower(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER);
        $I->haveInRepository($gameEquipment);

        $dirtyStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::DIRTY]);
        $dirtyStatus = new Status($this->player1, $dirtyStatusConfig);
        $I->haveInRepository($dirtyStatus);

        $I->refreshEntities($this->player1);

        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $gameEquipment,
            player: $this->player1,
            target: $gameEquipment
        );

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost(),
            $this->player1->getActionPoint()
        );
        $I->assertCount(0, $this->player1->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testMushShower(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var ChargeStatusConfig $mushStatusConfig */
        $mushStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => PlayerStatusEnum::MUSH]);
        $mushStatus = new ChargeStatus($this->player1, $mushStatusConfig);
        $I->haveInRepository($mushStatus);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER);
        $I->haveInRepository($gameEquipment);

        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $gameEquipment,
            player: $this->player1,
            target: $gameEquipment
        );

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEqualsWithDelta($this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint() - 3.5, $this->player1->getHealthPoint(), 0.5);
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost(),
            $this->player1->getActionPoint()
        );

        $logs = $I->grabEntitiesFromRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_MUSH,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
        $I->assertCount(1, $logs);
    }

    public function testShowerWithSoap(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER);
        $I->haveInRepository($gameEquipment);

        /** @var VariableEventModifierConfig $soapModifierConfig */
        $soapModifierConfig = current(
            $I->grabEntitiesFromRepository(
                VariableEventModifierConfig::class,
                ['name' => 'modifier_for_player_-1actionPoint_on_shower']
            )
        );
        $soapModifier = new GameModifier($this->player2, $soapModifierConfig);
        $soapModifier->setModifierProvider($this->player2);
        $I->haveInRepository($soapModifier);

        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $gameEquipment,
            player: $this->player2,
            target: $gameEquipment
        );

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(
            $this->player2->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost() + 1,
            $this->player2->getActionPoint()
        );
        $I->assertCount(0, $this->player2->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player2->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function shouldGiveOneHealthPointOrOneMoralePointsOrTwoMovementPointsIfWithThalasso(FunctionalTester $I): void
    {
        // given a Thalasso in Chun's room
        $thalasso = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::THALASSO,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when Chun takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $thalasso,
            player: $this->chun,
            target: $thalasso
        );

        $logs = 0;
        $this->chun->setActionPoint(12);
        while ($this->chun->getActionPoint() > 0) {
            $this->showerAction->execute();

            // then a log should be printed
            if ($this->chun->getHealthPoint() === $this->chun->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint() + 1) {
                $I->seeInRepository(RoomLog::class, [
                    'place' => $this->chun->getPlace()->getName(),
                    'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                    'playerInfo' => $this->chun->getPlayerInfo()->getId(),
                    'log' => PlayerModifierLogEnum::GAIN_HEALTH_POINT,
                    'visibility' => VisibilityEnum::PRIVATE,
                ]);
                ++$logs;
            } if ($this->chun->getMoralPoint() === $this->chun->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint() + 1) {
                $I->seeInRepository(RoomLog::class, [
                    'place' => $this->chun->getPlace()->getName(),
                    'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                    'playerInfo' => $this->chun->getPlayerInfo()->getId(),
                    'log' => PlayerModifierLogEnum::GAIN_MORAL_POINT,
                    'visibility' => VisibilityEnum::PRIVATE,
                ]);
                ++$logs;
            } if ($this->chun->getMovementPoint() === $this->chun->getPlayerInfo()->getCharacterConfig()->getInitMovementPoint() + 2) {
                $I->seeInRepository(RoomLog::class, [
                    'place' => $this->chun->getPlace()->getName(),
                    'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                    'playerInfo' => $this->chun->getPlayerInfo()->getId(),
                    'log' => PlayerModifierLogEnum::GAIN_MOVEMENT_POINT,
                    'visibility' => VisibilityEnum::PRIVATE,
                ]);
                ++$logs;
            }

            if ($logs > 1) {
                $I->fail('Chun should have gained only one health point or one morale point or two movement points, not all three. Logs : ' . $logs);
            } elseif ($logs === 1) {
                break;
            }
        }
    }

    public function shouldNotGiveThalassoBonusToMushPlayers(FunctionalTester $I): void
    {
        // given a Thalasso in KT's room
        $thalasso = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::THALASSO,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given KT is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // when KT takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $thalasso,
            player: $this->kuanTi,
            target: $thalasso
        );
        $this->showerAction->execute();

        // then KT should not have gained any health point, morale point or movement point
        $expectedKTHealthPointAverage = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint() - 3.5; // -3 or -4 from Mush shower malus
        $expectedKTHealthPointDelta = 0.5;
        $expectedKTMoralePoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint();
        $expectedKTMovementPoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitMovementPoint();

        $I->assertEqualsWithDelta($expectedKTHealthPointAverage, $this->kuanTi->getHealthPoint(), $expectedKTHealthPointDelta);
        $I->assertEquals($expectedKTMoralePoint, $this->kuanTi->getMoralPoint());
        $I->assertEquals($expectedKTMovementPoint, $this->kuanTi->getMovementPoint());
    }

    public function splashproofMushPlayerShouldNotLoseHealthPoints(FunctionalTester $I): void
    {
        // given a shower in KT's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given KT is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // given KT has splashproof skill
        $this->addSkillToPlayer(SkillEnum::SPLASHPROOF, $I, $this->kuanTi);

        // when KT takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $shower,
            player: $this->kuanTi,
            target: $shower
        );
        $this->showerAction->execute();

        // then KT should not have lost any health point
        $expectedKTHealthPoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint();

        $I->assertEquals($expectedKTHealthPoint, $this->kuanTi->getHealthPoint());
    }

    public function splashproofMushPlayerShouldHaveNormalShowerLog(FunctionalTester $I): void
    {
        // given a shower in KT's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given KT is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // given KT has splashproof skill
        $this->addSkillToPlayer(SkillEnum::SPLASHPROOF, $I, $this->kuanTi);

        // when KT takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $shower,
            player: $this->kuanTi,
            target: $shower
        );
        $this->showerAction->execute();

        // then KT should not have lost any health point
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous vous lavez longuement sous la douche, savourant ce moment rare et paisible...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: ActionLogEnum::SHOWER_HUMAN,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function splashproofMushPlayerShouldNotGainThalassoBonus(FunctionalTester $I): void
    {
        // given a shower in KT's room
        $shower = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::THALASSO,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given KT is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // given KT has splashproof skill
        $this->addSkillToPlayer(SkillEnum::SPLASHPROOF, $I, $this->kuanTi);

        // when KT takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $shower,
            player: $this->kuanTi,
            target: $shower
        );
        $this->showerAction->execute();

        // then KT should not have gained any health point, morale point or movement point
        $expectedKTHealthPoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint();
        $expectedKTMoralePoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint();
        $expectedKTMovementPoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitMovementPoint();

        $I->assertEquals($expectedKTHealthPoint, $this->kuanTi->getHealthPoint());
        $I->assertEquals($expectedKTMoralePoint, $this->kuanTi->getMoralPoint());
        $I->assertEquals($expectedKTMovementPoint, $this->kuanTi->getMovementPoint());
    }

    public function shouldMakeAntiquePerfumePlayerImmunized(FunctionalTester $I): void
    {
        $this->givenPlayerHasAntiquePerfumeSkill($I);

        $this->whenPlayerTakesShower();

        $this->thenPlayerShouldBeImmunized($I);
    }

    public function shouldRemoveHumanSporeWithSuperSoap(FunctionalTester $I): void
    {
        $this->givenPlayerHasSpores(2);

        $this->givenPlayerHasSuperSoap();

        $this->whenPlayerTakesShower();

        $this->thenPlayerShouldHaveSpores(1, $I);
    }

    public function shouldRemoveHumanSporeWhenAntiquePerfumeImmunized(FunctionalTester $I): void
    {
        $this->givenPlayerHasAntiquePerfumeSkill($I);

        $this->givenPlayerHasSpores(2);

        $this->givenPlayerHasSuperSoap();

        $this->whenPlayerTakesShower();

        $this->thenPlayerShouldHaveSpores(1, $I);

        $this->whenPlayerTakesShower();

        $this->thenPlayerShouldHaveSpores(0, $I);
    }

    public function shouldNotRemoveMushSporeWithSuperSoap(FunctionalTester $I): void
    {
        $this->givenPlayerHasSpores(2);

        $this->givenPlayerHasSuperSoap();

        $this->givenPlayerIsMush();

        $this->whenPlayerTakesShower();

        $this->thenPlayerShouldHaveSpores(2, $I);
    }

    public function shouldCostOneLessActionPointWithSuperSoap(FunctionalTester $I): void
    {
        $this->givenActionCostIs(2);

        $this->givenPlayerHasSuperSoap();

        $this->whenPlayerTriesToTakeShower();

        $this->thenActionCostShouldBe(1, $I);
    }

    public function shouldCostOneLessActionPointWithSplashproof(FunctionalTester $I): void
    {
        $this->givenActionCostIs(2);

        $this->addSkillToPlayer(SkillEnum::SPLASHPROOF, $I, $this->player);

        $this->whenPlayerTriesToTakeShower();

        $this->thenActionCostShouldBe(1, $I);
    }

    public function splashproofAndSoapCantStack(FunctionalTester $I): void
    {
        $this->givenActionCostIs(2);

        $this->addSkillToPlayer(SkillEnum::SPLASHPROOF, $I, $this->player);

        $this->givenPlayerHasSuperSoap();

        $this->whenPlayerTriesToTakeShower();

        $this->thenActionCostShouldBe(1, $I);
    }

    private function givenPlayerHasAntiquePerfumeSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ANTIQUE_PERFUME, $I);
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores(2);
    }

    private function givenPlayerHasSuperSoap(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SUPER_SOAPER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenActionCostIs(int $actionCost): void
    {
        $this->action->setActionCost($actionCost);
    }

    private function whenPlayerTriesToTakeShower(): void
    {
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->shower,
            player: $this->player,
            target: $this->shower
        );
    }

    private function whenPlayerTakesShower(): void
    {
        $this->whenPlayerTriesToTakeShower();
        $this->showerAction->execute();
    }

    private function thenPlayerShouldBeImmunized(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::ANTIQUE_PERFUME_IMMUNIZED));
    }

    private function thenPlayerShouldHaveSpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->player->getSpores());
    }

    private function thenActionCostShouldBe(int $actionCost, FunctionalTester $I): void
    {
        $I->assertEquals($actionCost, $this->showerAction->getActionPointCost());
    }
}
