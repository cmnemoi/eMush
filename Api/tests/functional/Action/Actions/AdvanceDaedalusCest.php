<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AdvanceDaedalus;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Enum\DaedalusStatusEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class AdvanceDaedalusCest extends AbstractFunctionalTest
{
    private Action $advanceDaedalusConfig;
    private AdvanceDaedalus $advanceDaedalusAction;
    private GameEquipment $commandTerminal;
    private GameEquipment $emergencyReactor;
    private Place $bridge;
    private StatusServiceInterface $statusService;
    private AlertServiceInterface $alertService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->advanceDaedalusConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::ADVANCE_DAEDALUS]);
        $this->advanceDaedalusAction = $I->grabService(AdvanceDaedalus::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $this->alertService = $I->grabService(AlertServiceInterface::class);

        $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::TRAVELING]);

        // given there is a command terminal in the bridge
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($this->bridge);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig)
        ;
        $I->haveInRepository($this->commandTerminal);

        // given there is an emergency reactor in the engine room
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);
        $emergencyReactorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::EMERGENCY_REACTOR]);
        $this->emergencyReactor = new GameEquipment($engineRoom);
        $this->emergencyReactor
            ->setName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setEquipment($emergencyReactorConfig)
        ;
        $I->haveInRepository($this->emergencyReactor);

        // given the player is on the bridge
        $this->player->changePlace($this->bridge);

        // given there is fuel in combustion chamber
        $this->daedalus->setCombustionChamberFuel(1);

        // given the player is focused on the command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal
        );
    }

    public function testAdvanceDaedalusNotVisibleIfPlayerIsNotFocusedOnCommandTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the command terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to advance daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the action is not visible
        $I->assertFalse($this->advanceDaedalusAction->isVisible());
    }

    public function testAdvanceDaedalusSuccessCreatesADaedalusTravelingStatus(FunctionalTester $I): void
    {
        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the player has a daedalus traveling status
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::TRAVELING));
    }

    public function testAdvanceDaedalusSuccessKillAllPlayersInSpaceBattle(FunctionalTester $I): void
    {
        // given player2 is in space battle : in space for example
        $this->player2->changePlace($this->daedalus->getSpace());

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then player2 is dead
        $I->assertFalse($this->player2->isAlive());
    }

    public function testAdvanceDaedalusSuccessDoesNotKillPlayersNotInSpaceBattle(FunctionalTester $I): void
    {
        // given player2 is not in space battle : in laboratory for example
        $this->player2->changePlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then player2 is alive
        $I->assertTrue($this->player2->isAlive());
    }

    public function testAdvanceDaedalusSuccessDestroyAllPatrolShipsInSpaceBattle(FunctionalTester $I): void
    {
        // given a patrol ship is in space battle
        $pasiphaePlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($pasiphaePlace);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the patrol ship is destroyed
        $I->dontSeeInRepository(
            entity: GameEquipment::class,
            params: ['name' => EquipmentEnum::PASIPHAE]
        );
    }

    public function testAdvanceDaedalusSuccessDoesNotDestroyPatrolShipsNotInSpaceBattle(FunctionalTester $I): void
    {
        // given a patrol ship is not in space battle, let's say on the bridge
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->bridge);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the patrol ship is not destroyed
        $I->seeInRepository(
            entity: GameEquipment::class,
            params: ['name' => EquipmentEnum::PASIPHAE]
        );
    }

    public function testAdvanceDaedalusSuccessDestroyAllItemsInSpace(FunctionalTester $I): void
    {
        // given there is some metal scrap in space
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->daedalus->getSpace());
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig)
        ;
        $I->haveInRepository($metalScrap);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the metal scrap is destroyed
        $I->dontSeeInRepository(
            entity: GameEquipment::class,
            params: ['name' => ItemEnum::METAL_SCRAPS]
        );
    }

    public function testAdvanceDaedalusSuccessCreatesANeronAnnouncement(FunctionalTester $I): void
    {
        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then there is a neron announcement
        $I->seeInRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::TRAVEL_DEFAULT]
        );
    }

    public function testAdvanceDaedalusFailsIfNoFuelInCombustionChamber(FunctionalTester $I): void
    {
        // given there is no fuel in the combustion chamber
        $this->daedalus->setCombustionChamberFuel(0);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $result = $this->advanceDaedalusAction->execute();

        // then the action fails
        $I->assertInstanceOf(Fail::class, $result);
    }

    public function testAdvanceDaedalusNoFuelReturnsSpecificLog(FunctionalTester $I): void
    {
        // given there is no fuel in the combustion chamber
        $this->daedalus->setCombustionChamberFuel(0);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the action returns the correct log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => ActionLogEnum::ADVANCE_DAEDALUS_NO_FUEL,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testAdvanceDaedalusFailsIfThereIsAnArackAttacking(FunctionalTester $I): void
    {
        // given there is an arack attacking
        /** @var HunterConfig $arackConfig */
        $arackConfig = $this->daedalus->getGameConfig()->getHunterConfigs()->getHunter(HunterEnum::SPIDER);

        $arack = new Hunter($arackConfig, $this->daedalus);
        $arack->setHunterVariables($arackConfig);
        $this->daedalus->addHunter($arack);

        $I->haveInRepository($arack);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $result = $this->advanceDaedalusAction->execute();

        // then the action fails
        $I->assertInstanceOf(Fail::class, $result);
    }

    public function testAdvanceDaedalusArackAttackingReturnsSpecificLog(FunctionalTester $I): void
    {
        // given there is an arack attacking
        $arack = $this->createHunterByName(HunterEnum::SPIDER, $I);

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $this->advanceDaedalusAction->execute();

        // then the action returns the correct log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => ActionLogEnum::ADVANCE_DAEDALUS_ARACK_PREVENTS_TRAVEL,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testAdvanceDaedalusFailsIfEmergencyReactorIsBroken(FunctionalTester $I): void
    {
        // given the emergency reactor is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->emergencyReactor,
            tags: [],
            time: new \DateTime(),
        );

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters($this->advanceDaedalusConfig, $this->player, $this->commandTerminal);
        $result = $this->advanceDaedalusAction->execute();

        // then the action fails
        $I->assertInstanceOf(Fail::class, $result);
    }

    public function testAdvanceDaedalusEmergencyReactorBrokenReturnsSpecificLog(FunctionalTester $I): void
    {
        // given the emergency reactor is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->emergencyReactor,
            tags: [],
            time: new \DateTime(),
        );

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters(
            action: $this->advanceDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->advanceDaedalusAction->execute();

        // then the action returns the correct log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => ActionLogEnum::ADVANCE_DAEDALUS_EMERGENCY_REACTOR_BROKEN,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testAdvanceSuccessPutAllSimpleHuntersInPool(FunctionalTester $I): void
    {
        // given there are 2 simple hunters attacking
        for ($i = 0; $i < 2; ++$i) {
            $this->createHunterByName(HunterEnum::HUNTER, $I);
        }

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters(
            action: $this->advanceDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->advanceDaedalusAction->execute();

        // then the 2 simple hunters are in the pool
        $I->assertEquals(2, $this->daedalus->getHunterPool()->getAllHuntersByType(HunterEnum::HUNTER)->count());
    }

    public function testAdvanceSuccessDoNotPutTraxsInPool(FunctionalTester $I): void
    {
        // given there are 2 traxs attacking
        for ($i = 0; $i < 2; ++$i) {
            $this->createHunterByName(HunterEnum::TRAX, $I);
        }

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters(
            action: $this->advanceDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->advanceDaedalusAction->execute();

        // then the 2 traxs are not in the pool
        $I->assertEquals(0, $this->daedalus->getHunterPool()->getAllHuntersByType(HunterEnum::TRAX)->count());
        $I->assertEquals(2, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::TRAX)->count());
    }

    public function testAdvanceSuccessDeletesAllAttackingHuntersExceptHuntersAndTraxes(FunctionalTester $I): void
    {
        // given there are 1 simple hunters attacking
        $hunter = $this->createHunterByName(HunterEnum::HUNTER, $I);
        // given there are traxs attacking
        $trax = $this->createHunterByName(HunterEnum::TRAX, $I);

        // given there is 2 dices attacking
        for ($i = 0; $i < 2; ++$i) {
            $dice = $this->createHunterByName(HunterEnum::DICE, $I);
        }

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters(
            action: $this->advanceDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->advanceDaedalusAction->execute();

        // then I can see 1 hunter and 1 trax in the repository. I can't see any dice
        $I->seeInRepository(
            entity: Hunter::class,
            params: ['hunterConfig' => $hunter->getHunterConfig()]
        );
        $I->seeInRepository(
            entity: Hunter::class,
            params: ['hunterConfig' => $trax->getHunterConfig()]
        );
        $I->dontSeeInRepository(
            entity: Hunter::class,
            params: ['hunterConfig' => $dice->getHunterConfig()]
        );
    }

    public function testAdvanceDaedalusNotExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters(
            action: $this->advanceDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->advanceDaedalusAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_TRAVELING, $this->advanceDaedalusAction->cannotExecuteReason());
    }

    public function testAdvanceDaedalusRemoveFuelInCombustionChamber(FunctionalTester $I): void
    {
        // when player advances daedalus
        $this->advanceDaedalusAction->loadParameters(
            action: $this->advanceDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->advanceDaedalusAction->execute();

        // then there is no fuel in the combustion chamber
        $I->assertEquals(0, $this->daedalus->getCombustionChamberFuel());
    }

    private function createHunterByName(string $hunterName, FunctionalTester $I): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $this->daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);

        $hunter = new Hunter($hunterConfig, $this->daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $this->daedalus->addHunter($hunter);

        $I->haveInRepository($hunter);

        $this->alertService->handleHunterArrival($this->daedalus);

        return $hunter;
    }
}
