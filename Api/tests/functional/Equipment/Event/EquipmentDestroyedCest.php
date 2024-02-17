<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Service;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class EquipmentDestroyedCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
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

        // when the invertebrate shell is destroyed by a fire
        $equipmentEvent = new EquipmentEvent(
            $invertebrateShell,
            false,
            VisibilityEnum::PUBLIC,
            [EventEnum::FIRE],
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // then the turret should be broken
        $I->assertTrue($turret->isBroken());

        // then the scooter should be broken
        $I->assertTrue($scooter->isBroken());

        // then the metal scraps should not be broken (not breakable)
        $I->assertFalse($scraps->isBroken());

        // then the itrackie in the player's inventory should not be broken (in player's inventory)
        $I->assertFalse($itrackieInInventory->isBroken());
    }
}
