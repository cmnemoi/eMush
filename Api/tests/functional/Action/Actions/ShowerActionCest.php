<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Shower;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierNameEnum;
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

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->showerAction = $I->grabService(Shower::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SHOWER]);
        $this->action->setInjuryRate(0);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
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

        /** @var VariableEventModifierConfig $mushShowerModifierConfig */
        $mushShowerModifierConfig = current($I->grabEntitiesFromRepository(
            TriggerEventModifierConfig::class,
            [
                'name' => ModifierNameEnum::MUSH_SHOWER_MALUS, ]
        ));
        $mushShowerModifier = new GameModifier($this->player1, $mushShowerModifierConfig);
        $mushShowerModifier->setModifierProvider($this->player1);
        $I->haveInRepository($mushShowerModifier);

        $I->refreshEntities($this->player1);

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

        $I->assertEquals($this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint() - 3, $this->player1->getHealthPoint());
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
                ['name' => 'soapShowerActionModifier']
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

        $chunInitialHealthPoint = $this->chun->getHealthPoint();
        $chunInitialMoralePoint = $this->chun->getMoralPoint();
        $chunInitialMovementPoint = $this->chun->getMovementPoint();

        // when Chun takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $thalasso,
            player: $this->chun,
            target: $thalasso
        );
        $this->showerAction->execute();

        // then Chun should have gained one health point or one morale point or two movement points
        $I->assertTrue(
            $this->chun->getHealthPoint() === $chunInitialHealthPoint + 1
            || $this->chun->getMoralPoint() === $chunInitialMoralePoint + 1
            || $this->chun->getMovementPoint() === $chunInitialMovementPoint + 2
        );
    }

    public function shouldGiveOnlyOneThalassoBonus(FunctionalTester $I): void
    {
        // given a Thalasso in Chun's room
        $thalasso = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::THALASSO,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        $chunInitialHealthPoint = $this->chun->getHealthPoint();
        $chunInitialMoralePoint = $this->chun->getMoralPoint();
        $chunInitialMovementPoint = $this->chun->getMovementPoint();

        // when Chun takes a shower
        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $thalasso,
            player: $this->chun,
            target: $thalasso
        );
        $this->showerAction->execute();

        // then Chun should have gained only one health point or one morale point or two movement points
        if ($this->chun->getHealthPoint() === $chunInitialHealthPoint + 1) {
            $I->assertEquals($chunInitialMoralePoint, $this->chun->getMoralPoint());
            $I->assertEquals($chunInitialMovementPoint, $this->chun->getMovementPoint());
        } elseif ($this->chun->getMoralPoint() === $chunInitialMoralePoint + 1) {
            $I->assertEquals($chunInitialHealthPoint, $this->chun->getHealthPoint());
            $I->assertEquals($chunInitialMovementPoint, $this->chun->getMovementPoint());
        } elseif ($this->chun->getMovementPoint() === $chunInitialMovementPoint + 2) {
            $I->assertEquals($chunInitialHealthPoint, $this->chun->getHealthPoint());
            $I->assertEquals($chunInitialMoralePoint, $this->chun->getMoralPoint());
        } else {
            $I->fail('Chun should have gained only one health point or one morale point or two movement points, not all three.');
        }
    }

    public function shouldPrintALogAboutThalassoBonus(FunctionalTester $I): void
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
        } elseif ($this->chun->getMoralPoint() === $this->chun->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint() + 1) {
            $I->seeInRepository(RoomLog::class, [
                'place' => $this->chun->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->chun->getPlayerInfo()->getId(),
                'log' => PlayerModifierLogEnum::GAIN_MORAL_POINT,
                'visibility' => VisibilityEnum::PRIVATE,
            ]);
        } elseif ($this->chun->getMovementPoint() === $this->chun->getPlayerInfo()->getCharacterConfig()->getInitMovementPoint() + 2) {
            $I->seeInRepository(RoomLog::class, [
                'place' => $this->chun->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->chun->getPlayerInfo()->getId(),
                'log' => PlayerModifierLogEnum::GAIN_MOVEMENT_POINT,
                'visibility' => VisibilityEnum::PRIVATE,
            ]);
        } else {
            $I->fail('Chun should have gained only one health point or one morale point or two movement points, not all three.');
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
        $expectedKTHealthPoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint() - 3; // -3 from Mush shower malus
        $expectedKTMoralePoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint();
        $expectedKTMovementPoint = $this->kuanTi->getPlayerInfo()->getCharacterConfig()->getInitMovementPoint();

        $I->assertEquals($expectedKTHealthPoint, $this->kuanTi->getHealthPoint());
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
}
