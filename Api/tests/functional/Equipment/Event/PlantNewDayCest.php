<?php

namespace Mush\Tests\functional\Equipment\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PlantNewDayCest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testPlantHealthy(FunctionalTester $I)
    {
        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);

        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->buildName(GameConfigEnum::TEST)
            ->setMaxCharge(10)
        ;
        $I->haveInRepository($plantYoung);
        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDiseased);
        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDry);
        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantThirsty);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE)
        ;
        $I->haveInRepository($fruitMechanic);
        $plantMechanic = new Plant();
        $plantMechanic
            ->setName('plantMechanic')
            ->setFruitName(GameFruitEnum::BANANA)
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $hydropotConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);
        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
            'equipmentName' => GameFruitEnum::BANANA,
        ]);
        /** @var EquipmentConfig $plantConfig */
        $plantConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$plantMechanic]),
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$plantYoung, $plantThirsty, $plantDry, $plantDiseased, $fireStatusConfig]),
            'equipmentsConfig' => new ArrayCollection([$fruitConfig, $plantConfig, $hydropotConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(18);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'space']);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => 'test']);
        $characterConfig
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
        ;
        $I->haveInRepository($characterConfig);
        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $plant = $plantConfig->createGameEquipment($room);
        $I->haveInRepository($plant);

        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(15, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());

        $daedalus->setCycle(8);
        $I->flushToDatabase($daedalus);

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(13, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(2, $room->getEquipments());
        $I->assertTrue($plant->hasStatus(EquipmentStatusEnum::PLANT_THIRSTY));
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testPlantThirsty(FunctionalTester $I)
    {
        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);
        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->buildName(GameConfigEnum::TEST)
            ->setMaxCharge(10)
        ;
        $I->haveInRepository($plantYoung);
        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDiseased);
        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDry);
        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantThirsty);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE)
        ;
        $I->haveInRepository($fruitMechanic);
        $plantMechanic = new Plant();
        $plantMechanic
            ->setName('plantMechanic')
            ->setFruitName(GameFruitEnum::BANANA)
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $hydropotConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);
        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
            'equipmentName' => GameFruitEnum::BANANA,
        ]);
        /** @var EquipmentConfig $plantConfig */
        $plantConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$plantMechanic]),
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$plantYoung, $plantThirsty, $plantDry, $plantDiseased, $fireStatusConfig]),
            'equipmentsConfig' => new ArrayCollection([$fruitConfig, $plantConfig, $hydropotConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(18);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'space']);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => 'test']);
        $characterConfig
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
            ->setInitMoralPoint(99)
            ->setMaxMoralPoint(99)
        ;
        $I->haveInRepository($characterConfig);
        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $plant = $plantConfig->createGameEquipment($room);
        $I->haveInRepository($plant);

        $status = new Status($plant, $plantThirsty);
        $I->haveInRepository($status);

        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(15, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());

        $daedalus->setCycle(8);
        $I->flushToDatabase($daedalus);

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(13, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());
        $I->assertFalse($plant->hasStatus(EquipmentStatusEnum::PLANT_THIRSTY));
        $I->assertTrue($plant->hasStatus(EquipmentStatusEnum::PLANT_DRY));
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testPlantDry(FunctionalTester $I)
    {
        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);
        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantYoung);
        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDiseased);
        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDry);
        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantThirsty);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE)
        ;
        $I->haveInRepository($fruitMechanic);
        $plantMechanic = new Plant();
        $plantMechanic
            ->setName('plantMechanic')
            ->setFruitName(GameFruitEnum::BANANA)
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $hydropotConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);
        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
            'equipmentName' => GameFruitEnum::BANANA,
        ]);
        /** @var EquipmentConfig $plantConfig */
        $plantConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$plantMechanic]),
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$plantYoung, $plantThirsty, $plantDry, $plantDiseased, $fireStatusConfig]),
            'equipmentsConfig' => new ArrayCollection([$fruitConfig, $plantConfig, $hydropotConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(18);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'space']);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => 'test']);
        $characterConfig
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
            ->setInitMoralPoint(99)
            ->setMaxMoralPoint(99)
        ;
        $I->haveInRepository($characterConfig);
        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $plant = $plantConfig->createGameEquipment($room);
        $I->haveInRepository($plant);

        $status = new Status($plant, $plantDry);
        $I->haveInRepository($status);

        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(15, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());

        $daedalus->setCycle(8);
        $I->flushToDatabase($daedalus);

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(12, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());
        $I->assertEquals($room->getEquipments()->first()->getName(), ItemEnum::HYDROPOT);
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => PlantLogEnum::PLANT_DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testPlantGrow(FunctionalTester $I)
    {
        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);
        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantYoung);
        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDiseased);
        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDry);
        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantThirsty);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE)
        ;
        $I->haveInRepository($fruitMechanic);
        $plantMechanic = new Plant();
        $plantMechanic
            ->setName('plantMechanic')
            ->setFruitName(GameFruitEnum::BANANA)
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $hydropotConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);
        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
            'equipmentName' => GameFruitEnum::BANANA,
        ]);
        /** @var EquipmentConfig $plantConfig */
        $plantConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$plantMechanic]),
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$plantYoung, $plantThirsty, $plantDry, $plantDiseased, $fireStatusConfig]),
            'equipmentsConfig' => new ArrayCollection([$fruitConfig, $plantConfig, $hydropotConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(18);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'space']);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => 'test']);
        $characterConfig
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
            ->setInitMoralPoint(99)
            ->setMaxMoralPoint(99)
        ;
        $I->haveInRepository($characterConfig);
        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $plant = $plantConfig->createGameEquipment($room);
        $I->haveInRepository($plant);

        $plantYoung->setStartCharge(0);
        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $plantYoung,
            $plant,
            [],
            new \DateTime()
        );

        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );

        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(15, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());
        $I->assertTrue($plant->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));
        $I->assertEquals($status->getCharge(), 1);

        $daedalus->setCycle(8);
        $I->flushToDatabase($daedalus);

        // Plant young do not produce oxygen
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        $I->assertEquals(12, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(1, $room->getEquipments());
        $I->assertTrue($plant->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));
        $I->assertEquals($status->getCharge(), 2);

        $status->setCharge(10);
        $I->flushToDatabase($status);
        $daedalus->setCycle(8);
        $I->flushToDatabase($daedalus);
    }

    public function testPlantGrowAndProduces(FunctionalTester $I)
    {
        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);
        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setStatusName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->buildName(GameConfigEnum::TEST)
            ->setMaxCharge(10)
        ;
        $I->haveInRepository($plantYoung);
        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDiseased);
        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantDry);
        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($plantThirsty);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE)
        ;
        $I->haveInRepository($fruitMechanic);
        $plantMechanic = new Plant();
        $plantMechanic
            ->setName('plantMechanic')
            ->setFruitName(GameFruitEnum::BANANA)
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $I->haveInRepository($plantMechanic);

        /** @var EquipmentConfig $hydropotConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);
        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
            'equipmentName' => GameFruitEnum::BANANA,
        ]);
        /** @var EquipmentConfig $plantConfig */
        $plantConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$plantMechanic]),
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$plantYoung, $plantThirsty, $plantDry, $plantDiseased, $fireStatusConfig]),
            'equipmentsConfig' => new ArrayCollection([$fruitConfig, $plantConfig, $hydropotConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(18);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'space']);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::CHUN . '_test']);
        $characterConfig
            ->setInitHealthPoint(99)
            ->setMaxHealthPoint(99)
        ;
        $I->haveInRepository($characterConfig);
        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $plant = $plantConfig->createGameEquipment($room);
        $I->haveInRepository($plant);

        $plantYoung->setStartCharge(10);
        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $plantYoung,
            $plant,
            [],
            new \DateTime()
        );

        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );

        $daedalus->setCycle(8);
        $I->flushToDatabase($daedalus);

        // Plant grow it should produce oxygen just after that
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
        $I->assertEquals(16, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertCount(2, $room->getEquipments());
        $I->assertFalse($plant->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => PlantLogEnum::PLANT_MATURITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
