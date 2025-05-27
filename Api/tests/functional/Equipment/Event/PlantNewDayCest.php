<?php

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlantNewDayCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    // produce oxygen, fruit, log

    public function healthyPlantShouldProduceOxygen(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 8 oxygen (-3 base + 1 per healthy plant)
        $I->assertEquals(8, $this->daedalus->getOxygen());
    }

    public function healthyPlantShouldProduceFruit(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a banana fruit is created
        $I->assertTrue($this->chun->getPlace()->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    public function healthyPlantShouldProduceLog(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a log is created
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function thirstyPlantShouldProduceOxygen(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given banana tree is thirsty
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_THIRSTY,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 8 oxygen (-3 base + 1 per thirsty plant)
        $I->assertEquals(8, $this->daedalus->getOxygen());
    }

    public function thirstyPlantShouldNotProduceFruit(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given banana tree is thirsty
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_THIRSTY,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a banana fruit is not created
        $I->assertFalse($this->chun->getPlace()->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    public function thirstyPlantShouldProduceNotLog(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given banana tree is thirsty
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_THIRSTY,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then no log is created
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function dryPlantShouldNotProduceOxygen(FunctionalTester $I): void
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given banana tree is dry
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DRY,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 7 oxygen (-3 base + 0 per dry plant)
        $I->assertEquals(7, $this->daedalus->getOxygen());
    }

    public function dryPlantShouldNotProduceFruit(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given banana tree is dry
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DRY,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a banana fruit is not created
        $I->assertFalse($this->chun->getPlace()->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    public function dryPlantShouldNotProduceLog(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree is mature
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // given banana tree is dry
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DRY,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then no log is created
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function youngPlantShouldNotProduceOxygen(FunctionalTester $I): void
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 7 oxygen (-3 base + 0 per young plant)
        $I->assertEquals(7, $this->daedalus->getOxygen());
    }

    public function youngPlantShouldNotProduceFruit(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a banana fruit is not created
        $I->assertFalse($this->chun->getPlace()->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    public function youngPlantShouldNotProduceLog(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // when day change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then no log is created
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function youngPlantShouldGetMatureAtMaturationCycle(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree has 35 maturation cycles so it matures at next cycle
        $bananaTree->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG)->setCharge(35);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then banana tree is mature
        $I->assertFalse($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));
    }

    public function youngPlantShouldProduceOxygenAtMaturationCycle(FunctionalTester $I): void
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree has 35 maturation cycles so it matures at next cycle
        $bananaTree->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG)->setCharge(35);

        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 8 oxygen (-3 base + 1 per healthy plant)
        $I->assertEquals(8, $this->daedalus->getOxygen());
    }

    public function youngPlantShouldProduceFruitAtMaturationCycle(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree has 35 maturation cycles so it matures at next cycle
        $bananaTree->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG)->setCharge(35);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a banana fruit is created
        $I->assertTrue($this->chun->getPlace()->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    public function youngPlantShouldProduceLogAtMaturationCycle(FunctionalTester $I)
    {
        // given a banana tree
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given banana tree has 35 maturation cycles so it matures at next cycle
        $bananaTree->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG)->setCharge(35);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a log is created
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => PlantLogEnum::PLANT_MATURITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
