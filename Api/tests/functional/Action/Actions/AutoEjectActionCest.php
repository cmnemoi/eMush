<?php

declare(strict_types=1);

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
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

    public function testAutoEjectSuccess(FunctionalTester $I)
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
