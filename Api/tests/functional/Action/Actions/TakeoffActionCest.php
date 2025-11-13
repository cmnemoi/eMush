<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class TakeoffActionCest extends AbstractFunctionalTest
{
    private Takeoff $takeoffAction;
    private ActionConfig $action;

    private SpaceShip $pasiphae;
    private ChargeStatus $pasiphaeArmor;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Player $terrence;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->terrence = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::TERRENCE);

        $this->pasiphae = $this->givenThereIsAPasipahe($I, $this->daedalus);

        $this->pasiphaeArmor = $this->pasiphae->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        // given Terrence is a pilot so they can take off
        $this->addSkillToPlayer(SkillEnum::PILOT, $I, $this->terrence);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKEOFF]);
        $this->takeoffAction = $I->grabService(Takeoff::class);
    }

    public function playerShouldNotBeAbleToTakeOffWithPasiphaeIfNotAPilot(FunctionalTester $I): void
    {
        // given KT is not a pilot (default)
        // given crewlock is on pilot (default)

        // when KT tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->kuanTi,
            target: $this->pasiphae
        );

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK, $this->takeoffAction->cannotExecuteReason());
    }

    public function playerShouldNotBeAbleToTakeOffWithPatrolShipIfNotAPilot(FunctionalTester $I): void
    {
        // given KT is not a pilot (default)
        // given crewlock is on pilot (default)

        // given there is a patrol ship
        $patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(EquipmentEnum::PATROL_SHIP, $this->player->getPlace(), [], new \DateTime());

        // when KT tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $patrolShip,
            player: $this->kuanTi,
            target: $patrolShip
        );

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK, $this->takeoffAction->cannotExecuteReason());
    }

    public function testTakeoffCriticalSuccess(FunctionalTester $I)
    {
        $this->action->setCriticalRate(100);
        $I->haveInRepository($this->action);

        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->terrence,
            target: $this->pasiphae
        );
        $I->assertTrue($this->takeoffAction->isVisible());
        $I->assertNull($this->takeoffAction->cannotExecuteReason());

        $result = $this->takeoffAction->execute();

        $I->assertEquals(
            $this->terrence->getActionPoint(),
            $this->terrence->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );

        $I->assertInstanceOf(CriticalSuccess::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->terrence->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertEquals(
            $this->terrence->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->terrence->getDaedalus()->getHull()
        );
        $I->assertEquals(
            $this->terrence->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->terrence->getHealthPoint()
        );
        $I->assertEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->terrence->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertFalse($this->takeoffAction->isVisible());
    }

    public function testTakeoffSuccess(FunctionalTester $I): void
    {
        $this->action->setCriticalRate(0);
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->chun,
            target: $this->pasiphae
        );
        $I->assertTrue($this->takeoffAction->isVisible());
        $I->assertNull($this->takeoffAction->cannotExecuteReason());

        $result = $this->takeoffAction->execute();

        $I->assertEquals(
            $this->chun->getActionPoint(),
            $this->chun->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->assertNotEquals(
            $this->chun->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->chun->getHealthPoint()
        );
        $I->assertNotEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chun->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chun->getPlayerInfo(),
            'log' => LogEnum::PATROL_DAMAGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertNotEquals(
            $this->chun->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->chun->getDaedalus()->getHull()
        );
    }

    public function testTakeOffNotExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::TRAVELING,
            $this->daedalus,
            [],
            new \DateTime()
        );

        // when player tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->terrence,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_TRAVELING, $this->takeoffAction->cannotExecuteReason());
    }

    public function playerShouldNotBeAbleToTakeOffIfPatrolShipIsBroken(FunctionalTester $I): void
    {
        // given the patrol ship (Pasiphae) is broken
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $this->pasiphae,
            [],
            new \DateTime()
        );

        // when a pilot (Terrence) tries to take off with the broken ship
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->terrence,
            target: $this->pasiphae
        );

        // then the action cannot be executed
        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->takeoffAction->cannotExecuteReason());
    }

    public function testTakeOffActionDropCriticalItems(FunctionalTester $I): void
    {
        // given player has the extinguisher in their inventory
        $takeOffRoom = $this->player->getPlace();
        $extinguisherConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::EXTINGUISHER]);
        $extinguisher = new GameItem($this->player);
        $extinguisher
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setEquipment($extinguisherConfig);
        $I->haveInRepository($extinguisher);

        // when player tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->terrence,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then the extinguisher is dropped in the take off room
        $I->assertFalse($this->terrence->hasEquipmentByName(ToolItemEnum::EXTINGUISHER));
    }

    public function testTakeOffActionDropCriticalItemsIfPlayerIsMush(FunctionalTester $I): void
    {
        // given player has the extinguisher and the hacker kit in their inventory
        $extinguisherConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::EXTINGUISHER]);
        $extinguisher = new GameItem($this->terrence);
        $extinguisher
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setEquipment($extinguisherConfig);
        $I->haveInRepository($extinguisher);

        $hackerKitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::HACKER_KIT]);
        $hackerKit = new GameItem($this->terrence);
        $hackerKit
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setEquipment($hackerKitConfig);
        $I->haveInRepository($hackerKit);

        // given player is Mush
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::MUSH,
            $this->terrence,
            [],
            new \DateTime()
        );

        // when player tries to take off
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->terrence,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then the hacker kit is dropped in the take off room but not the extinguisher
        $I->assertFalse($this->terrence->hasEquipmentByName(ToolItemEnum::HACKER_KIT));
        $I->assertTrue($this->terrence->hasEquipmentByName(ToolItemEnum::EXTINGUISHER));
    }

    public function shouldDestroyPatrolShipIfNoEnoughArmor(FunctionalTester $I): void
    {
        // given NERON crew lock is not on piloting so non-pilots can take off
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then pasiphae is destroyed
        $I->dontSeeInRepository(GameEquipment::class, ['name' => EquipmentEnum::PASIPHAE]);
    }

    public function shouldKillPlayerWithoutSpacesuitIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given NERON crew lock is not on piloting so non-pilots can take off
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then player is dead from patrol ship explosion
        $I->assertEquals(
            expected: EndCauseEnum::PATROL_SHIP_EXPLOSION,
            actual: $this->player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }

    public function shouldNotKillPlayerWithSpacesuitIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given NERON crew lock is not on piloting so non-pilots can take off
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then player is alive
        $I->assertTrue($this->player->isAlive());
    }

    public function shouldMovePatrolShipContentInSpaceIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given NERON crew lock is not on piloting so non-pilots can take off
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at takeoffing
        $this->pasiphaeArmor->setCharge(1);

        // given there is an old shirt in pasiphae
        $oldShirt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OLD_T_SHIRT,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::PASIPHAE),
            reasons: [],
            time: new \DateTime()
        );

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then old shirt is in space
        $I->assertEquals(
            expected: RoomEnum::SPACE,
            actual: $oldShirt->getPlace()->getName(),
        );
    }

    public function shouldKillPlayerByInjuryIfTheyDontHaveHealthPoints(FunctionalTester $I): void
    {
        // given NERON crew lock is not on piloting so non-pilots can take off
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        // given takeoff action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given player has only one health point
        $this->player->setHealthPoint(1);

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then player is dead from injury
        $I->assertEquals(
            expected: EndCauseEnum::INJURY,
            actual: $this->player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }

    public function shouldHaveIncreasedChanceOfCriticalSuccessWithBayDoorXXLProject(FunctionalTester $I): void
    {
        // given NERON crew lock is not on piloting so non-pilots can take off
        $this->setNeronCrewLock(NeronCrewLockEnum::NULL);

        // given takeoff action has a 75% critical rate
        $this->action->setCriticalRate(75);

        // given Bay Door XXL project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::BAY_DOOR_XXL),
            author: $this->player,
            I: $I
        );

        // when player takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
        $result = $this->takeoffAction->execute();

        // then takeoff should always be a critical success thanks to increased chances
        $I->assertInstanceOf(CriticalSuccess::class, $result);
    }

    public function shouldCostOneLessActionPointWithPatrolShipLauncherProject(FunctionalTester $I): void
    {
        // given takeoff action cost is 2 action points
        $I->assertEquals(2, $this->action->getActionCost());

        // given terrence has 2 action points
        $this->terrence->setActionPoint(2);

        // given Patrol ship launcher project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATROL_SHIP_LAUNCHER),
            author: $this->terrence,
            I: $I
        );

        // when terrence takeoffs
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->terrence,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();

        // then terrence should have 1 action point left
        $I->assertEquals(1, $this->terrence->getActionPoint());
    }

    public function shouldCreateALogWhenDroppingCriticalItem(FunctionalTester $I): void
    {
        $this->givenChunIsAPilot($I);

        $this->givenChunHasStainproofApron();

        $this->whenChunTakeoffs();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** a lâché un **Tablier intachable**.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::DROP_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldCreateANotificationWhenDroppingCriticalItem(FunctionalTester $I): void
    {
        $this->givenChunIsAPilot($I);

        $this->givenChunHasStainproofApron();

        $this->whenChunTakeoffs();

        $this->thenChunShouldReceiveANotification($I);
    }

    private function givenChunIsAPilot(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::PILOT, $I);
    }

    private function givenChunHasStainproofApron(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenChunTakeoffs(): void
    {
        $this->takeoffAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->chun,
            target: $this->pasiphae
        );
        $this->takeoffAction->execute();
    }

    private function thenChunShouldReceiveANotification(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $this->chun->getFirstNotificationOrThrow()->getMessage(),
            actual: PlayerNotificationEnum::DROPPED_CRITICAL_ITEM->toString(),
        );
    }

    private function setNeronCrewLock(NeronCrewLockEnum $crewLock): void
    {
        $neron = $this->daedalus->getNeron();
        (new \ReflectionProperty($neron, 'crewLock'))->setValue($neron, $crewLock);
    }

    private function givenThereIsAPasipahe(FunctionalTester $I, Daedalus $daedalus): SpaceShip
    {
        $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $daedalus);

        return $this->gameEquipmentService->createGameEquipmentFromName(EquipmentEnum::PASIPHAE, $this->player->getPlace(), [], new \DateTime());
    }
}
