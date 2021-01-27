<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusCycleSubscriber;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;

class PlantCycleEventCest
{
    private DaedalusCycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(DaedalusCycleSubscriber::class);
    }

    public function testPlantGrowing(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'plantDiseaseRate' => 0,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'name' => 'fruit']);

        $plantMechanic = new Plant();
        $plantMechanic
            ->setMaturationTime([8 => 1])
            ->setOxygen([1 => 1])
            ->setFruit($fruitConfig)
        ;
        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$plantMechanic])]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('plant name')
            ->setRoom($room)
        ;

        $I->haveInRepository($gameEquipment);

        $youngStatus = new ChargeStatus($gameEquipment);
        $youngStatus
            ->setName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCharge(6)
            ->setStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setThreshold(8)
        ;

        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertEquals(7, $room->getEquipments()->first()->getStatuses()->first()->getCharge());

        //growing up
        $time = new DateTime();
        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(0, $room->getEquipments()->first()->getStatuses());
    }

    public function testPlantChangeDay(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'plantDiseaseRate' => 0,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'cycle' => 8, 'oxygen' => 10]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'name' => 'fruit']);

        $plantMechanic = new Plant();
        $plantMechanic
            ->setMaturationTime([8 => 1])
            ->setOxygen([1 => 1])
            ->setFruit($fruitConfig)
        ;

        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'mechanics' => new ArrayCollection([$plantMechanic])]);

        /* @var EquipmentConfig $equipmentConfig */
        $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'name' => ItemEnum::HYDROPOT]);

        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('plant name')
            ->setRoom($room)
        ;

        $I->haveInRepository($gameEquipment);

        $youngStatus = new ChargeStatus($gameEquipment);
        $youngStatus
            ->setName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCharge(6)
            ->setStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setThreshold(8)
        ;

        //Plant is young : no fruit or oxygen
        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertEquals(9, $daedalus->getOxygen());

        //Plant is diseased
        $daedalus->setCycle(8);

        $diseasedStatus = new Status($gameEquipment);
        $diseasedStatus
            ->setName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $gameEquipment->removeStatus($youngStatus);

        $thirstyStatus = $gameEquipment->getStatuses()->first();
        $gameEquipment->removeStatus($thirstyStatus);

        $time = new DateTime();
        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(2, $room->getEquipments()->first()->getStatuses());
        $I->assertEquals(8, $daedalus->getOxygen());

        //Plant is thirsty
        $daedalus->setCycle(8);

        $gameEquipment->removeStatus($diseasedStatus);

        $time = new DateTime();
        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertEquals(8, $daedalus->getOxygen());

        //Plant is totally healthy
        $daedalus->setCycle(8);

        $thirstyStatus = $gameEquipment->getStatuses()->first();
        $gameEquipment->removeStatus($thirstyStatus);

        $time = new DateTime();
        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(2, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertEquals('fruit', $room->getEquipments()->next()->getName());
        $I->assertEquals(8, $daedalus->getOxygen());

        //Plant is dried
        /** @var Room $room */
        $room2 = $I->have(Room::class, ['daedalus' => $daedalus, 'name' => 'corridor']);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setEquipment($equipmentConfig)
            ->setName('plant name')
            ->setRoom($room2)
        ;

        $I->haveInRepository($gameEquipment2);

        $daedalus->setCycle(8);

        $driedOutStatus = new Status($gameEquipment2);
        $driedOutStatus
            ->setName(EquipmentStatusEnum::PLANT_DRIED_OUT)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $time = new DateTime();
        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room2->getStatuses());
        $I->assertCount(1, $room2->getEquipments());
        $I->assertEquals(ItemEnum::HYDROPOT, $room2->getEquipments()->first()->getName());
        $I->assertEquals(8, $daedalus->getOxygen());
    }
}
