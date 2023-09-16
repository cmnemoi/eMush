<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AutoEject;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class AutoEjectActionCest extends AbstractFunctionalTest
{
    private AutoEject $autoEjectAction;
    private Action $actionConfig;
    private GameEquipment $pasiphae;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $this->pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($this->pasiphae);

        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));

        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::AUTO_EJECT]);

        $this->autoEjectAction = $I->grabService(AutoEject::class);
    }

    public function testAutoEjectNotAvailableIfNoSpaceSuitInPlayerInventory(FunctionalTester $I): void
    {
        // given a player having no space suit in their inventory
        $I->assertEmpty($this->player1->getEquipments());

        // when we load the auto eject action
        $this->autoEjectAction->loadParameters($this->actionConfig, $this->player1, $this->pasiphae);

        // then player should not see the action
        $I->assertFalse($this->autoEjectAction->isVisible());
    }

    public function testAutoEjectNotAvailableIfNotInAPatrolShip(FunctionalTester $I): void
    {
        // given a player having a space suit in their inventory but not in a patrol ship
        $spaceSuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spaceSuit = new GameItem($this->player1);
        $spaceSuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spaceSuitConfig)
        ;
        $I->haveInRepository($spaceSuit);
        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));

        // when we load the auto eject action
        $this->autoEjectAction->loadParameters($this->actionConfig, $this->player1, $this->pasiphae);

        // then player should not see the action
        $I->assertFalse($this->autoEjectAction->isVisible());
    }

    public function testAutoEjectNotAvailableIfBrokenSpaceSuitInPlayerInventory(FunctionalTester $I): void
    {
        // given a player have a broken space suit in their inventory
        $spaceSuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spaceSuit = new GameItem($this->player1);
        $spaceSuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spaceSuitConfig)
        ;
        $I->haveInRepository($spaceSuit);

        $brokenStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $brokenStatus = new Status($spaceSuit, $brokenStatusConfig);
        $I->haveInRepository($brokenStatus);

        // when we load the auto eject action
        $this->autoEjectAction->loadParameters($this->actionConfig, $this->player1, $this->pasiphae);

        // then player should not see the action
        $I->assertFalse($this->autoEjectAction->isVisible());
    }

    public function testAutoEjectSuccess(FunctionalTester $I): void
    {
        // given a player having a space suit in their inventory
        $spaceSuitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GearItemEnum::SPACESUIT]);
        $spaceSuit = new GameItem($this->player1);
        $spaceSuit
            ->setName(GearItemEnum::SPACESUIT)
            ->setEquipment($spaceSuitConfig)
        ;
        $I->haveInRepository($spaceSuit);

        // when the player auto ejects
        $this->autoEjectAction->loadParameters($this->actionConfig, $this->player1, $this->pasiphae);
        $I->assertTrue($this->autoEjectAction->isVisible());
        $this->autoEjectAction->execute();

        // then player should be in space and we should see a success room log with public visibility in pasiphae room
        $I->assertEquals(expected: RoomEnum::SPACE, actual: $this->player1->getPlace()->getName());
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::AUTO_EJECT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        /** @var PlaceConfig $pasiphaeRoomConfig */
        $pasiphaeRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PASIPHAE]);
        $pasiphaeRoom = new Place();
        $pasiphaeRoom
            ->setName(RoomEnum::PASIPHAE)
            ->setType($pasiphaeRoomConfig->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($pasiphaeRoom);

        $I->haveInRepository($daedalus);
    }
}
