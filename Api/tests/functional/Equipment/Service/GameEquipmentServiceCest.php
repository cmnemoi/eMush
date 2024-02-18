<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Service;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class GameEquipmentServiceCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testInvertebrateShellBreaksAllEquipmentInRoomAfterBeingDestroyedByAFire(FunctionalTester $I): void
    {
        // given I have a turret in the room
        $turret = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TURRET_COMMAND,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given I have a scooter in the room
        $scooter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::ANTIGRAV_SCOOTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given I have metal scraps in the room
        $scraps = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given I have an itrackie in player's inventory
        $itrackieInInventory = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given I have an invertebrate shell in the room
        $invertebrateShell = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::INVERTEBRATE_SHELL,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given fire has a 100% chance to destroy equipment
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setEquipmentFireBreakRate(100);

        // when the invertebrate shell is destroyed by a fire
        $this->gameEquipmentService->handleBreakFire($invertebrateShell, new \DateTime());

        // then the turret should be broken
        $I->assertTrue($turret->isBroken());

        // then the scooter should be broken
        $I->assertTrue($scooter->isBroken());

        // then the metal scraps should not be broken (not breakable)
        $I->assertFalse($scraps->isBroken());

        // then the itrackie in the player's inventory should not be broken (in player's inventory)
        $I->assertFalse($itrackieInInventory->isBroken());

        // then I should see a public room log telling that the shell exploded
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::INVERTEBRATE_SHELL_EXPLOSION,
            ]
        );

        // then I should see a public room logs telling that the turret and the scooter have been broken
        $logs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => StatusEventLogEnum::EQUIPMENT_BROKEN,
            ]
        );
        $I->assertCount(2, $logs);
    }
}
