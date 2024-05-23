<?php

declare(strict_types=1);

namespace Mush\Tests;

use Mush\Action\Actions\AbstractMoveDaedalusAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractMoveDaedalusActionCest extends AbstractFunctionalTest
{
    protected ActionConfig $moveDaedalusActionConfig;
    protected AbstractMoveDaedalusAction $moveDaedalusAction;
    protected GameEquipment $commandTerminal;
    protected GameEquipment $emergencyReactor;
    protected StatusServiceInterface $statusService;
    protected PlanetServiceInterface $planetService;
    private AlertServiceInterface $alertService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private Place $bridge;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $this->alertService = $I->grabService(AlertServiceInterface::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::TRAVELING]);
        $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => DaedalusStatusEnum::IN_ORBIT]);

        // given there is a command terminal in the bridge
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($this->bridge);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig);
        $I->haveInRepository($this->commandTerminal);

        // given there is an emergency reactor in the engine room
        $engineRoom = $this->createExtraPlace(RoomEnum::ENGINE_ROOM, $I, $this->daedalus);
        $emergencyReactorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::EMERGENCY_REACTOR]);
        $this->emergencyReactor = new GameEquipment($engineRoom);
        $this->emergencyReactor
            ->setName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setEquipment($emergencyReactorConfig);
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

    public function testMoveDaedalusActionNotVisibleIfPlayerIsNotFocusedOnCommandTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the command terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to move daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the action is not visible
        $I->assertFalse($this->moveDaedalusAction->isVisible());
    }

    public function testMoveDaedalusActionSuccessCreatesADaedalusTravelingStatus(FunctionalTester $I): void
    {
        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the player has a daedalus traveling status
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::TRAVELING));
    }

    public function testMoveDaedalusActionSuccessKillAllPlayersInSpaceBattle(FunctionalTester $I): void
    {
        // given player2 is in space battle : in space for example
        $this->player2->changePlace($this->daedalus->getSpace());

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then player2 is dead
        $I->assertFalse($this->player2->isAlive());
    }

    public function testMoveDaedalusActionSuccessDoesNotKillPlayersNotInSpaceBattle(FunctionalTester $I): void
    {
        // given player2 is not in space battle : in laboratory for example
        $this->player2->changePlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then player2 is alive
        $I->assertTrue($this->player2->isAlive());
    }

    public function testMoveDaedalusActionSuccessDoesNotKillDeadPlayersInPatrolShip(FunctionalTester $I): void
    {
        // given player2 is in a patrol ship
        $pasiphaePlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($pasiphaePlace);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);
        $this->player2->changePlace($pasiphaePlace);

        // given player2 is dead
        $this->player2->getPlayerInfo()->setGameStatus(GameStatusEnum::CLOSED);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

        // then there is no Neron death announcement
        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getDaedalusInfo()->getNeron(),
                'message' => NeronMessageEnum::PLAYER_DEATH,
            ]
        );
    }

    public function testMoveDaedalusActionSuccessDestroyAllPatrolShipsInSpaceBattle(FunctionalTester $I): void
    {
        // given a patrol ship is in space battle
        $pasiphaePlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($pasiphaePlace);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the patrol ship is destroyed
        $I->dontSeeInRepository(
            entity: GameEquipment::class,
            params: ['name' => EquipmentEnum::PASIPHAE]
        );
    }

    public function testMoveDaedalusActionSuccessDoesNotDestroyPatrolShipsNotInSpaceBattle(FunctionalTester $I): void
    {
        // given a patrol ship is not in space battle, let's say on the bridge
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->bridge);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig);
        $I->haveInRepository($pasiphae);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the patrol ship is not destroyed
        $I->seeInRepository(
            entity: GameEquipment::class,
            params: ['name' => EquipmentEnum::PASIPHAE]
        );
    }

    public function testMoveDaedalusActionSuccessDestroyAllItemsInSpace(FunctionalTester $I): void
    {
        // given there is some metal scrap in space
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->daedalus->getSpace());
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig);
        $I->haveInRepository($metalScrap);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the metal scrap is destroyed
        $I->dontSeeInRepository(
            entity: GameEquipment::class,
            params: ['name' => ItemEnum::METAL_SCRAPS]
        );
    }

    public function testMoveDaedalusActionFailsIfNoFuelInCombustionChamber(FunctionalTester $I): void
    {
        // given there is no fuel in the combustion chamber
        $this->daedalus->setCombustionChamberFuel(0);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $result = $this->moveDaedalusAction->execute();

        // then the action fails
        $I->assertInstanceOf(Fail::class, $result);
    }

    public function testMoveDaedalusActionNoFuelReturnsSpecificLog(FunctionalTester $I): void
    {
        // given there is no fuel in the combustion chamber
        $this->daedalus->setCombustionChamberFuel(0);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

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

    public function testMoveDaedalusActionFailsIfThereIsAnArackAttacking(FunctionalTester $I): void
    {
        // given there is an arack attacking
        /** @var HunterConfig $arackConfig */
        $arackConfig = $this->daedalus->getGameConfig()->getHunterConfigs()->getHunter(HunterEnum::SPIDER);

        $arack = new Hunter($arackConfig, $this->daedalus);
        $arack->setHunterVariables($arackConfig);
        $this->daedalus->addHunter($arack);

        $I->haveInRepository($arack);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $result = $this->moveDaedalusAction->execute();

        // then the action fails
        $I->assertInstanceOf(Fail::class, $result);
    }

    public function testMoveDaedalusActionArackAttackingReturnsSpecificLog(FunctionalTester $I): void
    {
        // given there is an arack attacking
        $arack = $this->createHunterByName(HunterEnum::SPIDER, $I);

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

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

    public function testMoveDaedalusActionFailsIfEmergencyReactorIsBroken(FunctionalTester $I): void
    {
        // given the emergency reactor is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->emergencyReactor,
            tags: [],
            time: new \DateTime(),
        );

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $result = $this->moveDaedalusAction->execute();

        // then the action fails
        $I->assertInstanceOf(Fail::class, $result);
    }

    public function testMoveDaedalusActionEmergencyReactorBrokenReturnsSpecificLog(FunctionalTester $I): void
    {
        // given the emergency reactor is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->emergencyReactor,
            tags: [],
            time: new \DateTime(),
        );

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

        // then the action returns the correct log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => ActionLogEnum::ADVANCE_DAEDALUS_FAIL,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testMoveDaedalusActionPutAllSimpleHuntersInPool(FunctionalTester $I): void
    {
        // given there are 2 simple hunters attacking
        for ($i = 0; $i < 2; ++$i) {
            $this->createHunterByName(HunterEnum::HUNTER, $I);
        }

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

        // then the 2 simple hunters are in the pool
        $I->assertEquals(2, $this->daedalus->getHunterPool()->getAllHuntersByType(HunterEnum::HUNTER)->count());
    }

    public function testMoveDaedalusActionDoNotPutTraxsInPool(FunctionalTester $I): void
    {
        // given there are 2 traxs attacking
        for ($i = 0; $i < 2; ++$i) {
            $this->createHunterByName(HunterEnum::TRAX, $I);
        }

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

        // then the 2 traxs are not in the pool
        $I->assertEquals(0, $this->daedalus->getHunterPool()->getAllHuntersByType(HunterEnum::TRAX)->count());
        $I->assertEquals(2, $this->daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::TRAX)->count());
    }

    public function testMoveDaedalusActionDeletesAllAttackingHuntersExceptHuntersAndTraxes(FunctionalTester $I): void
    {
        // given there are 1 simple hunters attacking
        $hunter = $this->createHunterByName(HunterEnum::HUNTER, $I);
        // given there are traxs attacking
        $trax = $this->createHunterByName(HunterEnum::TRAX, $I);

        // given there is 2 dices attacking
        for ($i = 0; $i < 2; ++$i) {
            $dice = $this->createHunterByName(HunterEnum::DICE, $I);
        }

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

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

    public function testMoveDaedalusActionNotExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::DAEDALUS_TRAVELING, $this->moveDaedalusAction->cannotExecuteReason());
    }

    public function testMoveDaedalusActionRemoveFuelInCombustionChamber(FunctionalTester $I): void
    {
        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );

        $this->moveDaedalusAction->execute();

        // then there is no fuel in the combustion chamber
        $I->assertEquals(0, $this->daedalus->getCombustionChamberFuel());
    }

    public function shouldMakePasiphaeLandingWithMagneticNetProject(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::ALPHA_BAY_2, $I, $this->daedalus);

        // given Magnetic Net Project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET),
            author: $this->chun,
            I: $I
        );

        // given Pasiphae is in space battle
        $pasiphaePlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $pasiphae = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $pasiphaePlace,
            reasons: [],
            time: new \DateTime(),
        );

        // when player moves daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then pasiphae should be in Alpha Bay 2
        $I->assertEquals(RoomEnum::ALPHA_BAY_2, $pasiphae->getPlace()->getName());
    }

    protected function createHunterByName(string $hunterName, FunctionalTester $I): Hunter
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
