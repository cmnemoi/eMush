<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EquipmentCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldTransportFruitsProducedInGardenToRefectoryWithFoodRetailerProject(FunctionalTester $I): void
    {
        // given Food Retailer project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::FOOD_RETAILER),
            author: $this->player,
            I: $I,
        );

        // given garden
        $garden = $this->createExtraPlace(RoomEnum::HYDROPONIC_GARDEN, $I, $this->daedalus);

        // given refectory
        $refectory = $this->createExtraPlace(RoomEnum::REFECTORY, $I, $this->daedalus);

        // given a banana tree in garden
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $garden,
            reasons: [],
            time: new \DateTime(),
        );

        // given the banana tree is not young, so it will produce a banana at cycle change
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day changes
        $equipmentCycleEvent = new EquipmentCycleEvent(
            gameEquipment: $bananaTree,
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_DAY, EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentCycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        // then I should see a banana in refectory
        $I->assertTrue($refectory->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    public function shouldPrintALogInGardenWhenFoodRetrailerProjectTransportFruitToRefectory(FunctionalTester $I): void
    {
        // given Food Retailer project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::FOOD_RETAILER),
            author: $this->player,
            I: $I,
        );

        // given garden
        $garden = $this->createExtraPlace(RoomEnum::HYDROPONIC_GARDEN, $I, $this->daedalus);

        // given refectory
        $refectory = $this->createExtraPlace(RoomEnum::REFECTORY, $I, $this->daedalus);

        // given a banana tree in garden
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $garden,
            reasons: [],
            time: new \DateTime(),
        );

        // given the banana tree is not young, so it will produce a banana at cycle change
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day changes
        $equipmentCycleEvent = new EquipmentCycleEvent(
            gameEquipment: $bananaTree,
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_DAY, EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentCycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        // then I should see a log in garden
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::HYDROPONIC_GARDEN,
                'log' => LogEnum::FRUIT_TRANSPORTED,
                'visibility' => VisibilityEnum::PUBLIC,
            ],
        );
        $I->assertEquals(
            expected: GameFruitEnum::BANANA,
            actual: $log->getParameters()['target_item'],
        );
    }

    public function shouldPrintALogInRefectoryWhenFoodRetrailerProjectTransportFruitToRefectory(FunctionalTester $I): void
    {
        // given Food Retailer project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::FOOD_RETAILER),
            author: $this->player,
            I: $I,
        );

        // given garden
        $garden = $this->createExtraPlace(RoomEnum::HYDROPONIC_GARDEN, $I, $this->daedalus);

        // given refectory
        $refectory = $this->createExtraPlace(RoomEnum::REFECTORY, $I, $this->daedalus);

        // given a banana tree in garden
        $bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $garden,
            reasons: [],
            time: new \DateTime(),
        );

        // given the banana tree is not young, so it will produce a banana at cycle change
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $bananaTree,
            tags: [],
            time: new \DateTime(),
        );

        // when day changes
        $equipmentCycleEvent = new EquipmentCycleEvent(
            gameEquipment: $bananaTree,
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_DAY, EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentCycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        // then I should see a log in garden
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::REFECTORY,
                'log' => LogEnum::FRUIT_TRANSPORTED,
                'visibility' => VisibilityEnum::PUBLIC,
            ],
        );
        $I->assertEquals(
            expected: GameFruitEnum::BANANA,
            actual: $log->getParameters()['target_item'],
        );
    }
}
