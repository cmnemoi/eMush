<?php

namespace Mush\Tests\functional\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlantCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $equipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testPlantGrowing(FunctionalTester $I)
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setMaxCharge(8)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        $this->createProjects($I, $daedalus);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'name' => 'fruit_test',
            'equipmentName' => 'fruit',
        ]);

        $plantMechanic = new Plant();
        $plantMechanic
            ->setMaturationTime([8 => 1])
            ->setOxygen([1 => 1])
            ->setFruitName($fruitConfig->getEquipmentName())
            ->setName(GamePlantEnum::BANANA_TREE);
        $I->haveInRepository($plantMechanic);

        /** @var ItemConfig $equipmentConfig */
        $equipmentConfig = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$plantMechanic]),
        ]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(GamePlantEnum::BANANA_TREE);

        $I->haveInRepository($gameEquipment);

        $statusConfig->setStartCharge(6);

        /** @var ChargeStatus $youngStatus */
        $youngStatus = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $gameEquipment,
            [],
            new \DateTime()
        );

        $time = new \DateTime();

        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, [EventEnum::NEW_CYCLE], $time);

        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $gameEquipment->getStatuses());
        $I->assertEquals(7, $gameEquipment->getStatuses()->first()->getCharge());

        // growing up
        $time = new \DateTime();
        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, [EventEnum::NEW_CYCLE], $time);

        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        // then plant is not young anymore
        $I->assertCount(0, $room->getEquipments()->first()->getStatuses());

        // then I see a public maturation log
        /** @var RoomLog $roomLog */
        $roomLog = $I->grabEntityFromRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => PlantLogEnum::PLANT_MATURITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // then the log is correcly parametrized
        $logParameters = $roomLog->getParameters();
        $I->assertEquals($logParameters['item'], GamePlantEnum::BANANA_TREE);

        // then... actually, what is this assertion for ?
        $I->assertCount(0, $room->getStatuses());
    }

    public function testPlantChangeDay(FunctionalTester $I)
    {
        $thirstyStatusConfig = new StatusConfig();
        $thirstyStatusConfig
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($thirstyStatusConfig);
        $dryStatusConfig = new StatusConfig();
        $dryStatusConfig
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dryStatusConfig);
        $diseasedStatusConfig = new StatusConfig();
        $diseasedStatusConfig
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseasedStatusConfig);

        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'name' => 'fruit',
            'equipmentName' => 'fruit',
        ]);

        /** @var EquipmentConfig $equipmentConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => ItemEnum::HYDROPOT,
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'statusConfigs' => new ArrayCollection([$thirstyStatusConfig, $dryStatusConfig, $diseasedStatusConfig]),
            'equipmentsConfig' => new ArrayCollection([$fruitConfig, $hydropotConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycle' => 8]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(10);

        $this->createProjects($I, $daedalus);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $plantMechanic = new Plant();
        $plantMechanic
            ->setMaturationTime([8 => 1])
            ->setOxygen([1 => 1])
            ->setFruitName($fruitConfig->getEquipmentName())
            ->setName('plant_name');

        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['mechanics' => new ArrayCollection([$plantMechanic])]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('plant name');

        $I->haveInRepository($gameEquipment);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setMaxCharge(8)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        $statusConfig->setStartCharge(6);

        /** @var ChargeStatus $youngStatus */
        $youngStatus = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $gameEquipment,
            [],
            new \DateTime()
        );

        // Plant is young : no fruit or oxygen
        $time = new \DateTime();

        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, [EventEnum::PLANT_PRODUCTION, EventEnum::NEW_DAY], $time);

        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(2, $gameEquipment->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertTrue($gameEquipment->getStatuses()->exists(static fn (int $key, Status $value) => $value->getName() === EquipmentStatusEnum::PLANT_THIRSTY));
        $I->assertEquals(10, $daedalus->getOxygen());

        // Plant is diseased
        $diseasedStatus = new Status($gameEquipment, $diseasedStatusConfig);
        $I->haveInRepository($diseasedStatus);

        $gameEquipment->removeStatus($youngStatus);

        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, [EventEnum::PLANT_PRODUCTION, EventEnum::NEW_DAY], $time);

        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(0, $room->getStatuses());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(2, $gameEquipment->getStatuses());
        $I->assertTrue($gameEquipment->getStatuses()->exists(static fn (int $key, Status $value) => $value->getName() === EquipmentStatusEnum::PLANT_DRY));
        $I->assertEquals(10, $daedalus->getOxygen());

        // Plant is totally healthy
        $thirstyStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED);
        $gameEquipment->removeStatus($thirstyStatus);
        $thirstyStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::PLANT_DRY);
        $gameEquipment->removeStatus($thirstyStatus);

        $time = new \DateTime();
        $cycleEvent = new EquipmentCycleEvent($gameEquipment, $daedalus, [EventEnum::PLANT_PRODUCTION, EventEnum::NEW_DAY], $time);

        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(2, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertTrue($room->getEquipments()->exists(static fn (int $key, GameEquipment $item) => $item->getName() === 'fruit'));
        $I->assertEquals(11, $daedalus->getOxygen());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // Plant is dried
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'corridor']);

        $gameEquipment2 = new GameItem($room2);
        $gameEquipment2
            ->setEquipment($equipmentConfig)
            ->setName('plant name');

        $I->haveInRepository($gameEquipment2);

        $daedalus->setCycle(8);

        $driedOutStatus = new Status($gameEquipment2, $dryStatusConfig);
        $I->haveInRepository($driedOutStatus);

        $cycleEvent = new EquipmentCycleEvent(
            $gameEquipment2,
            $daedalus,
            [EventEnum::PLANT_PRODUCTION, EventEnum::NEW_DAY],
            new \DateTime()
        );

        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);

        $I->assertCount(0, $room2->getStatuses());
        $I->assertCount(1, $room2->getEquipments());
        $I->assertEquals(ItemEnum::HYDROPOT, $room2->getEquipments()->first()->getName());
        $I->assertEquals(11, $daedalus->getOxygen());
    }

    public function shouldMakePlantsGrowTwiceFasterInGardenWithHydroponicIncubatorProject(FunctionalTester $I): void
    {
        $this->givenThereIsAGardenInDaedalus($I);
        $bananaTree = $this->givenABananaTreeWithOneMaturationCycleInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);
        $this->givenHydroponicIncubatorProjectIsFinished($I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        $this->thenBananaTreeShouldBe(3, $bananaTree, $I);
    }

    private function givenThereIsAGardenInDaedalus(FunctionalTester $I): Place
    {
        return $this->createExtraPlace(
            placeName: RoomEnum::HYDROPONIC_GARDEN,
            I: $I,
            daedalus: $this->daedalus
        );
    }

    private function givenABananaTreeWithOneMaturationCycleInPlace(string $placeName, FunctionalTester $I): GameItem
    {
        $place = $this->daedalus->getPlaceByName($placeName);
        if ($place === null) {
            $this->createExtraPlace(
                placeName: $placeName,
                I: $I,
                daedalus: $this->daedalus
            );
        }

        $bananaTree = $this->equipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime()
        );

        $maturationStatus = $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG);
        $maturationStatus->setCharge(1);

        return $bananaTree;
    }

    private function givenHydroponicIncubatorProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::HYDROPONIC_INCUBATOR),
            author: $this->chun,
            I: $I
        );
    }

    private function whenCycleChangesForBananaTree(GameItem $bananaTree): void
    {
        $cycleEvent = new EquipmentCycleEvent($bananaTree, $this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
    }

    private function thenBananaTreeShouldBe(int $age, GameItem $bananaTree, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $age,
            actual: $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG)->getCharge()
        );
    }

    private function createProjects(FunctionalTester $I, Daedalus $daedalus): void
    {
        $projects = [ProjectName::PARASITE_ELIM, ProjectName::HEAT_LAMP, ProjectName::FOOD_RETAILER];
        foreach ($projects as $project) {
            $config = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => $project]);
            $project = new Project($config, $daedalus);
            $I->haveInRepository($project);
            $daedalus->addProject($project);
        }
    }
}
