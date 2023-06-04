<?php

declare(strict_types=1);

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;

class TakeoffActionCest extends AbstractFunctionalTest
{
    private Takeoff $takeoffAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::TAKEOFF]);

        $I->refreshEntities($this->action);

        $this->takeoffAction = $I->grabService(Takeoff::class);
    }

    public function testTakeoffSuccess(FunctionalTester $I)
    {
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        dump($this->player1->getPlace()->getName());
        dump($pasiphae->getPlace()->getName());

        $this->takeoffAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertTrue($this->takeoffAction->isVisible());
        $I->assertNull($this->takeoffAction->cannotExecuteReason());

        $result = $this->takeoffAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::LABORATORY,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::TAKEOFF_NO_PILOT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertFalse($this->takeoffAction->isVisible());
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

        $I->refreshEntities($daedalus);
    }
}
