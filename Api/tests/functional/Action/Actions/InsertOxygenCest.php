<?php

declare(strict_types=1);

namespace Mush\tests\Action\Actions;

use Mush\Action\Actions\InsertOxygen;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InsertOxygenCest extends AbstractFunctionalTest
{
    private InsertOxygen $insertOxygenAction;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private Action $actionConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $storageRoom = $this->createExtraPlace(RoomEnum::CENTER_ALPHA_STORAGE, $I, $this->daedalus);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::INSERT_OXYGEN]);

        $this->player->changePlace($storageRoom);

        $this->insertOxygenAction = $I->grabService(InsertOxygen::class);
    }

    public function testInsertOxygen(FunctionalTester $I)
    {
        // Given Daedalus has 3 oxygen
        $this->daedalus->setOxygen(3);

        // given there is a oxygen tank in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::OXYGEN_TANK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a oxygen capsule in inventory
        $gameCapsule = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OXYGEN_CAPSULE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // when player inserts the oxygen capsule into the oxygen tank
        $this->insertOxygenAction->loadParameters($this->actionConfig, $this->player, $gameCapsule);
        $this->insertOxygenAction->execute();

        // then the oxygen level of Daedalus should be increased by 1
        $I->assertEquals(4, $this->daedalus->getOxygen());

        // then the oxygen capsule should be removed from the player's inventory
        $I->assertEmpty($this->player->getEquipments());
    }
}
