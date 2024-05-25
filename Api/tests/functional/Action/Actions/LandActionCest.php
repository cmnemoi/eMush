<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Land;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class LandActionCest extends AbstractFunctionalTest
{
    private Land $landAction;
    private ActionConfig $action;
    private GameEquipment $pasiphae;
    private ChargeStatus $pasiphaeArmor;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->player1->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PASIPHAE));

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new GameEquipment($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PASIPHAE));
        $this->pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($this->pasiphae);

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);

        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmor = $statusService->createStatusFromName(
            EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            $this->pasiphae,
            [],
            new \DateTime()
        );

        $this->pasiphaeArmor = $pasiphaeArmor;

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::LAND]);

        $this->landAction = $I->grabService(Land::class);
    }

    public function testLandCriticalSuccess(FunctionalTester $I)
    {
        $this->action->setCriticalRate(100);
        $I->haveInRepository($this->action);

        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $I->assertTrue($this->landAction->isVisible());
        $I->assertNull($this->landAction->cannotExecuteReason());

        $result = $this->landAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );

        $I->assertInstanceOf(CriticalSuccess::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::LAND_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertEquals(
            $this->player1->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->player1->getDaedalus()->getHull()
        );
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );

        $I->assertEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::LAND_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertFalse($this->landAction->isVisible());
    }

    public function testLandFail(FunctionalTester $I)
    {
        $this->action->setCriticalRate(0);
        $I->haveInRepository($this->action);

        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $I->assertTrue($this->landAction->isVisible());
        $I->assertNull($this->landAction->cannotExecuteReason());

        $result = $this->landAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->assertNotEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->assertNotEquals(
            $this->pasiphaeArmor->getThreshold(),
            $this->pasiphaeArmor->getCharge()
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::LAND_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertNotEquals(
            $this->player1->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->player1->getDaedalus()->getHull()
        );
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::LAND_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DAMAGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertFalse($this->landAction->isVisible());
    }

    public function testLandFailWithPatrolShipDestroyedDoNotThrowLandingBayEquipmentInSpace(FunctionalTester $I): void
    {
        // given land action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at landing
        $this->pasiphaeArmor->setCharge(1);

        // given dynarcade is in alpha bay 2
        $dynarcadeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::DYNARCADE]);
        $dynarcade = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2));
        $dynarcade
            ->setName(EquipmentEnum::DYNARCADE)
            ->setEquipment($dynarcadeConfig);
        $I->haveInRepository($dynarcade);

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then dynarcade is still in alpha bay 2
        $I->assertEquals(
            $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2)->getName(),
            $dynarcade->getPlace()->getName()
        );
    }

    public function testLandSuccessPutAllPlayersInPatrolshipInTheBay(FunctionalTester $I): void
    {
        // given player 2 is in pasiphae
        $this->player2->changePlace($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));

        // when player 1 lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then player 2 is in alpha bay 2
        $I->assertEquals(
            $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2)->getName(),
            $this->player2->getPlace()->getName()
        );
    }

    public function shouldDestroyPatrolShipIfNoEnoughArmor(FunctionalTester $I): void
    {
        // given land action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at landing
        $this->pasiphaeArmor->setCharge(1);

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then pasiphae is destroyed
        $I->dontSeeInRepository(GameEquipment::class, ['name' => EquipmentEnum::PASIPHAE]);
    }

    public function shouldKillPlayerWithoutSpacesuitIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given land action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at landing
        $this->pasiphaeArmor->setCharge(1);

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then player is dead from patrol ship explosion
        $I->assertEquals(
            expected: EndCauseEnum::PATROL_SHIP_EXPLOSION,
            actual: $this->player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }

    public function shouldNotKillPlayerWithSpacesuitIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given land action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at landing
        $this->pasiphaeArmor->setCharge(1);

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then player is alive
        $I->assertTrue($this->player->isAlive());
    }

    public function shouldMovePatrolShipContentInSpaceIfPatrolShipExplodes(FunctionalTester $I): void
    {
        // given land action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given pasiphae armor is equals to one so it will be destroyed at landing
        $this->pasiphaeArmor->setCharge(1);

        // given there is an old shirt in pasiphae
        $oldShirt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OLD_T_SHIRT,
            equipmentHolder: $this->pasiphae->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then old shirt is in space
        $I->assertEquals(
            expected: RoomEnum::SPACE,
            actual: $oldShirt->getPlace()->getName(),
        );
    }

    public function shouldKillPlayerByInjuryIfTheyDontHaveHealthPoints(FunctionalTester $I): void
    {
        // given land action has a 0% critical rate so it will fail
        $this->action->setCriticalRate(0);

        // given player has only one health point
        $this->player1->setHealthPoint(1);

        // when player lands
        $this->landAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->pasiphae,
            player: $this->player1,
            target: $this->pasiphae
        );
        $this->landAction->execute();

        // then player is dead from injury
        $I->assertEquals(
            expected: EndCauseEnum::INJURY,
            actual: $this->player1->getPlayerInfo()->getClosedPlayer()->getEndCause(),
        );
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        /** @var PlaceConfig $pasiphaeRoomConfig */
        $pasiphaeRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PASIPHAE]);
        $pasiphaeRoom = new Place();
        $pasiphaeRoom
            ->setName(RoomEnum::PASIPHAE)
            ->setType($pasiphaeRoomConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($pasiphaeRoom);

        $alphaBay2Config = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::ALPHA_BAY_2]);
        $alphaBay2 = new Place();
        $alphaBay2
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setType($alphaBay2Config->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($alphaBay2);

        $I->haveInRepository($daedalus);
    }
}
