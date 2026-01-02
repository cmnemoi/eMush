<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\NPCTasks;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\NPCTasks\AiHandler\EvilDroneTaskHandler;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EvilDroneCest extends AbstractFunctionalTest
{
    private EvilDroneTaskHandler $evilDroneTaskHandler;

    private Drone $evilDrone;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    private Place $nexus;
    private Place $corridor;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->evilDroneTaskHandler = $I->grabService(EvilDroneTaskHandler::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        $this->nexus = $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);
        $this->corridor = $this->createExtraPlace(RoomEnum::REAR_CORRIDOR, $I, $this->daedalus);
        $storage = $this->createExtraPlace(RoomEnum::REAR_ALPHA_STORAGE, $I, $this->daedalus);

        Door::createFromRooms($this->nexus, $this->corridor);
        Door::createFromRooms($this->corridor, $storage);

        $this->evilDrone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::EVIL_DRONE,
            equipmentHolder: $storage,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function evilDroneShouldDoIdleTask(FunctionalTester $I)
    {
        // given drone can't have a target
        $this->evilDroneTaskHandler->setDoNothing(100);

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have moved in the next room and created the idle log
        $I->seeInRepository(RoomLog::class, ['place' => $this->corridor->getLogName(), 'log' => 'evil_drone.clean']);
    }

    public function evilDroneShouldConspireWithNeron(FunctionalTester $I)
    {
        // given drone can't be idle
        $this->evilDroneTaskHandler->setDoNothing(0);

        // given there is a NERON core in the nexus
        $this->gameEquipmentService->createGameEquipmentFromName(EquipmentEnum::NERON_CORE, $this->nexus, [], new \DateTime());

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have moved in the nexus and created the conspire log
        $I->seeInRepository(RoomLog::class, ['place' => $this->nexus->getLogName(), 'log' => 'evil_drone.conspire']);
    }

    public function evilDroneShouldFlirtWithPlayer(FunctionalTester $I)
    {
        // given drone can't be idle
        $this->evilDroneTaskHandler->setDoNothing(0);

        // given Chun is in the nexus
        $this->player->setPlace($this->nexus);

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have moved in the nexus and created the flirt log
        $I->seeInRepository(RoomLog::class, ['place' => $this->nexus->getLogName(), 'log' => 'evil_drone.flirt']);

        // then I should see flirted in the drone memory
        $I->assertEquals('flirted', $this->evilDrone->getStringFromMemory('chun'));
    }

    public function evilDroneShouldRecyclePlayer(FunctionalTester $I)
    {
        // given drone can't be idle
        $this->evilDroneTaskHandler->setDoNothing(0);

        // given Chun is in the nexus and is dead
        $this->player->setPlace($this->nexus);
        $this->playerService->persist($this->player);
        $this->playerService->killPlayer($this->player, EndCauseEnum::ROCKETED, new \DateTime());

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have moved in the nexus and created the recycle log and two cooked rations
        $I->seeInRepository(RoomLog::class, ['place' => $this->nexus->getLogName(), 'log' => 'evil_drone.recycle']);
        $I->assertCount(2, $this->nexus->getAllEquipmentsByName(GameRationEnum::COOKED_RATION));

        // then I should see recycled in the drone memory
        $I->assertEquals('recycled', $this->evilDrone->getStringFromMemory('chun'));
    }

    public function evilDroneShouldNotTryToRecyclePlayerInSpace(FunctionalTester $I)
    {
        // given drone can't be idle
        $this->evilDroneTaskHandler->setDoNothing(0);

        // given Chun is in the nexus and is dead
        $this->player->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
        $this->playerService->persist($this->player);
        $this->playerService->killPlayer($this->player, EndCauseEnum::ROCKETED, new \DateTime());

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have moved in the next room and created the idle log
        $I->seeInRepository(RoomLog::class, ['place' => $this->corridor->getLogName(), 'log' => 'evil_drone.clean']);

        $I->assertFalse($this->evilDrone->hasStatus(EquipmentStatusEnum::EVIL_DRONE_TARGET));
    }

    public function evilDroneShouldNotTargetPlayersTooFar(FunctionalTester $I)
    {
        // given drone can't be idle
        $this->evilDroneTaskHandler->setDoNothing(0);

        // given Chun is still in the laboratory

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have moved in the next room and created the idle log
        $I->seeInRepository(RoomLog::class, ['place' => $this->corridor->getLogName(), 'log' => 'evil_drone.clean']);

        // then it should not have any target
        $I->assertFalse($this->evilDrone->hasStatus(EquipmentStatusEnum::EVIL_DRONE_TARGET));
    }

    public function evilDroneShouldDoNothingWhenNotInARoom(FunctionalTester $I)
    {
        // given drone can't be idle
        $this->evilDroneTaskHandler->setDoNothing(0);

        // given drone is in space
        $this->evilDrone->setHolder($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
        $this->gameEquipmentService->persist($this->evilDrone);

        // when I activate it's handler
        $this->evilDroneTaskHandler->execute($this->evilDrone, new \DateTime());

        // then it should have created the kidnapping log
        $I->seeInRepository(RoomLog::class, ['place' => $this->evilDrone->getPlace()->getName(), 'log' => 'evil_drone.kidnapping']);
        // then it should not have any target
        $I->assertFalse($this->evilDrone->hasStatus(EquipmentStatusEnum::EVIL_DRONE_TARGET));
    }
}
