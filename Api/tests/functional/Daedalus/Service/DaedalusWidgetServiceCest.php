<?php

namespace functional\Daedalus\Service;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\AlertEnum;
use Mush\Daedalus\Service\DaedalusWidgetServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

class DaedalusWidgetServiceCest
{
    private DaedalusWidgetServiceInterface $daedalusWidgetService;

    public function _before(FunctionalTester $I)
    {
        $this->daedalusWidgetService = $I->grabService(DaedalusWidgetServiceInterface::class);
    }

    public function testNoAlerts(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);

        $alerts = $this->daedalusWidgetService->getAlerts($daedalus);

        $I->assertCount(1, $alerts);
        $I->assertEquals(AlertEnum::NO_ALERT, $alerts[0]['key']);
    }

    public function testNoOxygenAndHullAlert(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class, ['oxygen' => 5, 'hull' => 10]);

        $alerts = $this->daedalusWidgetService->getAlerts($daedalus);

        $I->assertCount(2, $alerts);
        $I->assertEquals(AlertEnum::LOW_OXYGEN, $alerts[0]['key']);
        $I->assertEquals(AlertEnum::LOW_HULL, $alerts[1]['key']);
    }

    public function testFireAlert(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $status = new Status($room);
        $status
            ->setName(StatusEnum::FIRE)
        ;
        $I->haveInRepository($status);

        $alerts = $this->daedalusWidgetService->getAlerts($daedalus);

        $I->assertCount(1, $alerts);
        $I->assertEquals(AlertEnum::NUMBER_FIRE, $alerts[0]['key']);
    }

    public function testBrokenAlert(FunctionalTester $I)
    {
        $daedalus = $I->have(Daedalus::class);
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $equipmentConfig = $I->have(EquipmentConfig::class);
        $itemConfig = $I->have(ItemConfig::class);

        $item = new GameItem();
        $item
            ->setName('item')
            ->setEquipment($itemConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($item);

        $door = new Door();
        $door
            ->setName('door')
            ->setEquipment($equipmentConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($door);

        $equipment = new GameEquipment();

        $equipment
            ->setName('equipment')
            ->setEquipment($equipmentConfig)
            ->setPlace($room)
        ;

        $I->haveInRepository($equipment);

        $status1 = new Status($equipment);
        $status1
            ->setName(EquipmentStatusEnum::BROKEN)
        ;

        $status2 = new Status($item);
        $status2
            ->setName(EquipmentStatusEnum::BROKEN)
        ;

        $status3 = new Status($door);
        $status3
            ->setName(EquipmentStatusEnum::BROKEN)
        ;

        $I->haveInRepository($status1);
        $I->haveInRepository($status2);
        $I->haveInRepository($status3);

        $alerts = $this->daedalusWidgetService->getAlerts($daedalus);

        $I->assertCount(2, $alerts);
        $I->assertEquals(AlertEnum::BROKEN_DOORS, $alerts[0]['key']);
        $I->assertEquals(AlertEnum::BROKEN_EQUIPMENTS, $alerts[1]['key']);
    }
}
