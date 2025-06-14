<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Transplant;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class TransplantActionCest extends AbstractFunctionalTest
{
    private Transplant $transplantAction;
    private ActionConfig $actionConfig;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'actionName' => ActionEnum::TRANSPLANT,
        ]);
        $this->transplantAction = $I->grabService(Transplant::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testTransplant(FunctionalTester $I)
    {
        $transplantAction = new ActionConfig();
        $transplantAction
            ->setActionName(ActionEnum::TRANSPLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($transplantAction);

        $fruitMechanic = new Fruit();
        $fruitMechanic
            ->addAction($transplantAction)
            ->setName('fruitMechanic')
            ->setPlantName(GamePlantEnum::BANANA_TREE);
        $I->haveInRepository($fruitMechanic);

        /** @var ItemConfig $hydropotConfig */
        $hydropotConfig = $I->have(ItemConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);

        /** @var ItemConfig $fruitConfig */
        $fruitConfig = $I->have(ItemConfig::class, [
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

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

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

        $fruit = new GameItem($room);
        $fruit
            ->setEquipment($fruitConfig)
            ->setName('fruit');
        $I->haveInRepository($fruit);

        $this->transplantAction->loadParameters($transplantAction, $fruit, $player, $fruit);

        $I->assertFalse($this->transplantAction->isVisible());

        $hydropot = new GameItem($room);
        $hydropot
            ->setEquipment($hydropotConfig)
            ->setName(ItemEnum::HYDROPOT);
        $I->haveInRepository($hydropot);

        $I->assertTrue($this->transplantAction->isVisible());

        $this->transplantAction->execute();

        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(1, $player->getEquipments());

        $I->seeInRepository(GameItem::class, ['name' => GamePlantEnum::BANANA_TREE]);
    }

    public function testTransplantCreatePlant(FunctionalTester $I)
    {
        $plantYoung = $I->grabEntityFromRepository(ChargeStatusConfig::class, [
            'statusName' => EquipmentStatusEnum::PLANT_YOUNG,
        ]);

        $transplantAction = new ActionConfig();
        $transplantAction
            ->setActionName(ActionEnum::TRANSPLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
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

        /** @var ItemConfig $hydropotConfig */
        $hydropotConfig = $I->have(ItemConfig::class, [
            'name' => 'hydropot_test',
            'equipmentName' => ItemEnum::HYDROPOT,
        ]);

        /** @var ItemConfig $fruitConfig */
        $fruitConfig = $I->have(ItemConfig::class, [
            'mechanics' => new ArrayCollection([$fruitMechanic]),
            'name' => 'fruit',
        ]);

        /** @var ItemConfig $plantConfig */
        $plantConfig = $I->have(ItemConfig::class, [
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

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

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

        $fruit = new GameItem($room);
        $fruit
            ->setEquipment($fruitConfig)
            ->setName('fruit');
        $I->haveInRepository($fruit);

        $this->transplantAction->loadParameters($transplantAction, $fruit, $player, $fruit);

        $I->assertFalse($this->transplantAction->isVisible());

        $hydropot = new GameItem($room);
        $hydropot
            ->setEquipment($hydropotConfig)
            ->setName(ItemEnum::HYDROPOT);
        $I->haveInRepository($hydropot);

        $I->assertTrue($this->transplantAction->isVisible());

        $this->transplantAction->execute();

        $I->assertCount(1, $player->getEquipments());

        $I->seeInRepository(GameItem::class, ['name' => GamePlantEnum::BANANA_TREE]);

        $tree = $player->getEquipments()->first();
        $I->assertInstanceOf(GameItem::class, $tree);
        $I->assertTrue($tree->hasStatus(EquipmentStatusEnum::PLANT_YOUNG));

        $status = $tree->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG);
        $I->assertInstanceOf(ChargeStatus::class, $status);
        $I->assertEquals(0, $status->getCharge());
        $I->assertEquals(15, $status->getVariableByName($status->getName())->getMaxValue());
    }

    public function shouldGiveNaturalistTriumphToIanWhenTransplantingAlienFruit(FunctionalTester $I): void
    {
        // Given
        $ian = $this->givenIanPlayerWithHydropot($I);
        $alienFruit = $this->givenAlienFruitInIanPlace($I, $ian);

        // When
        $this->whenIanTransplantsAlienFruit($alienFruit, $ian);

        // Then
        $this->thenIanShouldReceiveNaturalistTriumph($I, $ian);
    }

    private function givenIanPlayerWithHydropot(FunctionalTester $I): Player
    {
        $ian = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);

        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::HYDROPOT,
            equipmentHolder: $ian->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        return $ian;
    }

    private function givenAlienFruitInIanPlace(FunctionalTester $I, Player $ian): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::KUBINUS,
            equipmentHolder: $ian->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenIanTransplantsAlienFruit(GameItem $alienFruit, Player $ian): void
    {
        $this->transplantAction->loadParameters($this->actionConfig, $alienFruit, $ian, $alienFruit);
        $this->transplantAction->execute();
    }

    private function thenIanShouldReceiveNaturalistTriumph(FunctionalTester $I, Player $ian): void
    {
        $I->assertEquals(3, $ian->getTriumph());
    }
}
