<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusCycleSubscriber;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\DoorEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

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

        $gameEquipment->addStatus($youngStatus);



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

        $gameEquipment->addStatus($youngStatus);



        //Plant is young : no fruit or oxygen
        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertEquals(9, $daedalus->getOxygen());

        //Plant is is young but grow this cycle
        $daedalus->setCycle(8);

        $time = new DateTime();
        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);
        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(2, $room->getEquipments());
        $I->assertCount(EquipmentStatusEnum::PLANT_THIRSTY, $room->getEquipments()->first()->getStatuses()->first()->getName());
        $I->assertEquals(9, $daedalus->getOxygen());
    }
}
