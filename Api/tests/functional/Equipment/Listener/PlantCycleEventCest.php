<?php

namespace functional\Equipment\Listener;

use App\Tests\FunctionalTester;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlantCycleEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
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

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

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
            ->setPlace($room)
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

        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, $time);

        $this->eventDispatcher->dispatch($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $gameEquipment->getStatuses());
        $I->assertEquals(7, $gameEquipment->getStatuses()->first()->getCharge());

        //growing up
        $time = new DateTime();
        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, $time);

        $this->eventDispatcher->dispatch($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => PlantLogEnum::PLANT_MATURITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
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

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

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
            ->setPlace($room)
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

        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, $time);

        $this->eventDispatcher->dispatch($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);

        $I->assertCount(2, $gameEquipment->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertTrue($gameEquipment->getStatuses()->exists(fn (int $key, Status $value) => $value->getName() === EquipmentStatusEnum::PLANT_THIRSTY));
        $I->assertEquals(10, $daedalus->getOxygen());

        //Plant is diseased
        $diseasedStatus = new Status($gameEquipment);
        $diseasedStatus
            ->setName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $gameEquipment->removeStatus($youngStatus);

        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, $time);

        $this->eventDispatcher->dispatch($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(2, $gameEquipment->getStatuses());
        $I->assertTrue($gameEquipment->getStatuses()->exists(fn (int $key, Status $value) => $value->getName() === EquipmentStatusEnum::PLANT_DRY));
        $I->assertEquals(10, $daedalus->getOxygen());

        //Plant is totally healthy
        $thirstyStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED);
        $gameEquipment->removeStatus($thirstyStatus);
        $thirstyStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DRY);
        $gameEquipment->removeStatus($thirstyStatus);

        $time = new DateTime();
        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, $time);

        $this->eventDispatcher->dispatch($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);

        $I->assertCount(2, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertTrue($room->getEquipments()->exists(fn (int $key, GameEquipment $item) => $item->getName() === 'fruit'));
        $I->assertEquals(11, $daedalus->getOxygen());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        //Plant is dried
        /** @var Place $room */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'corridor']);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setEquipment($equipmentConfig)
            ->setName('plant name')
            ->setPlace($room2)
        ;

        $I->haveInRepository($gameEquipment2);

        $daedalus->setCycle(8);

        $driedOutStatus = new Status($gameEquipment2);
        $driedOutStatus
            ->setName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $time = new DateTime();
        $cycleEvent = new EquipmentCycleEvent($gameEquipment2, $daedalus, $time);

        $this->eventDispatcher->dispatch($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);

        $I->assertCount(0, $room2->getStatuses());
        $I->assertCount(1, $room2->getEquipments());
        $I->assertEquals(ItemEnum::HYDROPOT, $room2->getEquipments()->first()->getName());
        $I->assertEquals(11, $daedalus->getOxygen());
    }
}
