<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Daedalus\Service\DaedalusWidgetService;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusWidgetServiceCest extends AbstractFunctionalTest
{
    private DaedalusWidgetService $daedalusService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusWidgetService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $this->daedalus->getGameConfig()]);
        $iTrackie = new GameItem($this->player1);
        $iTrackie
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig);
        $I->haveInRepository($iTrackie);
    }

    public function testGetMinimap(FunctionalTester $I): void
    {
        $gravitySimulatorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR . '_default']);
        $gravitySimulator = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig);
        $I->haveInRepository($gravitySimulator);

        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->player1);

        $I->assertEmpty($minimap[RoomEnum::LABORATORY]['broken_equipments']);

        // break simulator
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $gravitySimulator,
            ['test'],
            new \DateTime()
        );

        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->player1);
        $I->assertEmpty($minimap[RoomEnum::LABORATORY]['broken_equipments']);

        // report equipment
        $reportEvent = new ApplyEffectEvent(
            $this->player1,
            $gravitySimulator,
            VisibilityEnum::PRIVATE,
            ['test'],
            new \DateTime(),
        );
        $this->eventService->callEvent($reportEvent, ApplyEffectEvent::REPORT_EQUIPMENT);

        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->player1);
        $I->assertCount(1, $minimap[RoomEnum::LABORATORY]['broken_equipments']);
    }

    public function shouldReturnFireWithFireSensor(FunctionalTester $I): void
    {
        // given fre sensor project is completed
        $fireDetector = $this->daedalus->getProjectByName(ProjectName::FIRE_SENSOR);
        $fireDetector->makeProgress(100);

        // given there is a broken piece of equipment in the laboratory
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            holder: $room,
            tags: [],
            time: new \DateTime(),
        );

        // when I get the minimap
        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->chun);

        // then the fire should be visible on the minimap
        $I->assertTrue($minimap[RoomEnum::LABORATORY]['fire']);
    }

    public function shouldReturnBrokenEquipmentWithEquipmentSensor(FunctionalTester $I): void
    {
        // given equipment sensor project is completed
        $equipmentDetector = $this->daedalus->getProjectByName(ProjectName::EQUIPMENT_SENSOR);
        $equipmentDetector->makeProgress(100);

        // given there is a broken piece of equipment in the laboratory
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $gravitySimulator = $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::GRAVITY_SIMULATOR,
            equipmentHolder: $room,
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            holder: $gravitySimulator,
            tags: [],
            time: new \DateTime(),
        );

        // when I get the minimap
        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->chun);

        // then the broken simulator should be visible on the minimap
        $I->assertCount(1, $minimap[RoomEnum::LABORATORY]['broken_equipments']);
    }

    public function shouldReturnBrokenDoorsWithEquipmentSensor(FunctionalTester $I): void
    {
        // given door sensor project is completed
        $doorDetector = $this->daedalus->getProjectByName(ProjectName::DOOR_SENSOR);
        $doorDetector->makeProgress(100);

        // given there is a broken door
        $door = $this->createDoorFromLaboratoryToFrontCorridor($I);
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime(),
        );

        // when I get the minimap
        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->chun);

        // then the broken door should be visible on the minimap
        $I->assertCount(1, $minimap[RoomEnum::LABORATORY]['broken_doors']);
    }

    private function createDoorFromLaboratoryToFrontCorridor(FunctionalTester $I): Door
    {
        $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = new Door($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR));
        $I->haveInRepository($door);

        return $door;
    }
}
