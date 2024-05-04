<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Transplant;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class TransplantActionCest
{
    private Transplant $transplantAction;

    public function _before(FunctionalTester $I)
    {
        $this->transplantAction = $I->grabService(Transplant::class);
    }

    public function testTransplant(FunctionalTester $I)
    {
        $transplantAction = new ActionConfig();
        $transplantAction
            ->setActionName(ActionEnum::TRANSPLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($transplantAction);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->addAction($transplantAction)
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE);
        $I->haveInRepository($fruitMechanic);

        /** @var EquipmentConfig $hydropotConfig */
        $hydropotConfig = $I->have(EquipmentConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);

        /** @var EquipmentConfig $fruitConfig */
        $fruitConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
        ]);

        /** @var ItemConfig $plantConfig */
        $plantConfig = $I->have(ItemConfig::class, [
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, [
            'name' => GameConfigEnum::DEFAULT,
        ]);
        $gameConfig->setEquipmentsConfig(new ArrayCollection([$plantConfig, $fruitConfig, $hydropotConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $player->setActionPoint(2)->setHealthPoint(6);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $fruit = new GameEquipment($room);
        $fruit
            ->setEquipment($fruitConfig)
            ->setName('fruit');
        $I->haveInRepository($fruit);

        $this->transplantAction->loadParameters($transplantAction, $player, $fruit);

        $I->assertFalse($this->transplantAction->isVisible());

        $hydropot = new GameEquipment($room);
        $hydropot
            ->setEquipment($hydropotConfig)
            ->setName(ItemEnum::HYDROPOT);
        $I->haveInRepository($hydropot);

        $I->assertTrue($this->transplantAction->isVisible());

        $this->transplantAction->execute();

        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(1, $player->getEquipments());

        $I->seeInRepository(GameEquipment::class, ['name' => GamePlantEnum::BANANA_TREE]);
    }

    public function testTransplantCreatePlant(FunctionalTester $I)
    {
        $plantYoung = $plantYoung = $I->grabEntityFromRepository(ChargeStatusConfig::class, [
            'statusName' => EquipmentStatusEnum::PLANT_YOUNG,
        ]);

        $transplantAction = new ActionConfig();
        $transplantAction
            ->setActionName(ActionEnum::TRANSPLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($transplantAction);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->addAction($transplantAction)
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE);
        $I->haveInRepository($fruitMechanic);

        $plantMechanic = new Plant();
        $plantMechanic
            ->setName('plantMechanic')
            ->setFruitName('banana')
            ->setMaturationTime([15 => 1]);
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
        ]);

        /** @var EquipmentConfig $plantConfig */
        $plantConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$plantMechanic]),
            'name' => 'banana_test',
            'equipmentName' => GamePlantEnum::BANANA_TREE,
        ]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, [
            'name' => GameConfigEnum::DEFAULT,
        ]);
        $gameConfig->setEquipmentsConfig(new ArrayCollection([$plantConfig, $fruitConfig, $hydropotConfig]));
        $gameConfig->setStatusConfigs(new ArrayCollection([$plantYoung]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $player->setActionPoint(2)->setHealthPoint(6);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $fruit = new GameEquipment($room);
        $fruit
            ->setEquipment($fruitConfig)
            ->setName('fruit');
        $I->haveInRepository($fruit);

        $this->transplantAction->loadParameters($transplantAction, $player, $fruit);

        $I->assertFalse($this->transplantAction->isVisible());

        $hydropot = new GameEquipment($room);
        $hydropot
            ->setEquipment($hydropotConfig)
            ->setName(ItemEnum::HYDROPOT);
        $I->haveInRepository($hydropot);

        $I->assertTrue($this->transplantAction->isVisible());

        $this->transplantAction->execute();

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getEquipments());

        $I->seeInRepository(GameEquipment::class, ['name' => GamePlantEnum::BANANA_TREE]);

        $tree = $room->getEquipments()->first();
        $I->assertInstanceOf(GameEquipment::class, $tree);
        $I->assertTrue($tree->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));

        $status = $tree->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG);
        $I->assertInstanceOf(ChargeStatus::class, $status);
        $I->assertEquals(0, $status->getCharge());
        $I->assertEquals(15, $status->getVariableByName($status->getName())->getMaxValue());
    }
}
