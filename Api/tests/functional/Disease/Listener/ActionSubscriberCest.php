<?php

namespace Mush\Tests\functional\Disease\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Entity\SymptomCondition;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Listener\ActionSubscriber;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;

class ActionSubscriberCest
{
    private ActionSubscriber $subscriber;

    public function _before(FunctionalTester $I)
    {
        $this->subscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionBreakoutsSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $moveActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionSymptomCondition
            ->setCondition(ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveActionSymptomCondition);

        $symptomConfig = new SymptomConfig('breakouts');
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionSymptomCondition)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $moveAction = $I->grabService(Move::class);

        $moveAction->loadParameters($moveActionEntity, $player, $door);
        $moveAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $room2,
            'log' => SymptomEnum::BREAKOUTS,
        ]);
    }

    public function testOnPostActionCatAllergySymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$takeActionEntity])]);

        $cat = new GameItem();
        $cat
            ->setName(ItemEnum::SCHRODINGER)
            ->setEquipment($itemConfig)
            ->setHolder($room)
        ;

        $I->haveInRepository($cat);

        $takeActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $takeActionSymptomCondition
            ->setCondition(ActionEnum::TAKE)
        ;

        $I->haveInRepository($takeActionSymptomCondition);

        $holdCatSymptomCondition = new SymptomCondition(SymptomConditionEnum::PLAYER_EQUIPMENT);
        $holdCatSymptomCondition
            ->setCondition(ItemEnum::SCHRODINGER);

        $I->haveInRepository($holdCatSymptomCondition);

        $symptomConfig = new SymptomConfig(SymptomEnum::CAT_ALLERGY);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($takeActionSymptomCondition)
            ->addSymptomCondition($holdCatSymptomCondition)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName(DiseaseEnum::CAT_ALLERGY)
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        // @TODO : fix me

        // $takeAction = $I->grabService(Take::class);

        // $takeAction->loadParameters($takeActionEntity, $player, $cat);
        // $takeAction->execute();

        // $I->seeInRepository(RoomLog::class, [
        //     'player' => $player,
        //     'place' => $room,
        //     'log' => SymptomEnum::CAT_ALLERGY
        // ]);
    }

    public function testOnPostActionDroolingSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $moveActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionSymptomCondition
            ->setCondition(ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveActionSymptomCondition);

        $symptomConfig = new SymptomConfig(SymptomEnum::DROOLING);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionSymptomCondition)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $moveAction = $I->grabService(Move::class);

        $moveAction->loadParameters($moveActionEntity, $player, $door);
        $moveAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $room2,
            'log' => SymptomEnum::DROOLING,
        ]);
    }

    public function testOnPostActionFoamingMouthSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $moveActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionSymptomCondition
            ->setCondition(ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveActionSymptomCondition);

        $symptomConfig = new SymptomConfig(SymptomEnum::FOAMING_MOUTH);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionSymptomCondition)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $moveAction = $I->grabService(Move::class);

        $moveAction->loadParameters($moveActionEntity, $player, $door);
        $moveAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $room2,
            'log' => SymptomEnum::FOAMING_MOUTH,
        ]);
    }

    public function testOnPostActionSneezingSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $moveActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionSymptomCondition
            ->setCondition(ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveActionSymptomCondition);

        $symptomConfig = new SymptomConfig(SymptomEnum::SNEEZING);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionSymptomCondition)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $moveAction = $I->grabService(Move::class);

        $moveAction->loadParameters($moveActionEntity, $player, $door);
        $moveAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $room2,
            'log' => SymptomEnum::SNEEZING,
        ]);
    }

    public function testOnPostActionVomitingSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setName(ActionEnum::CONSUME)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($consumeActionEntity);

        $consumeDrugActionEntity = new Action();
        $consumeDrugActionEntity
            ->setName(ActionEnum::CONSUME_DRUG)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($consumeDrugActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $ration = new Ration();
        $ration->setActions(new ArrayCollection([$consumeActionEntity]));
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setRation($ration)
        ;
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'place' => $room,
            'name' => 'ration',
        ]);

        $equipmentConfig
            ->setMechanics(new ArrayCollection([$ration]))
            ->setName('ration')
        ;

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem();
        $gameItem
            ->setHolder($room)
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
            'satiety' => 0,
        ]);

        $moveActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionSymptomCondition
            ->setCondition(ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveActionSymptomCondition);

        $consumeActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $consumeActionSymptomCondition
            ->setCondition(ActionEnum::CONSUME)
        ;

        $I->haveInRepository($consumeActionSymptomCondition);

        $moveVomitingConfig = new SymptomConfig(SymptomEnum::VOMITING);
        $moveVomitingConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionSymptomCondition)
        ;

        $I->haveInRepository($moveVomitingConfig);

        $consumeVomitingConfig = new SymptomConfig(SymptomEnum::VOMITING);
        $consumeVomitingConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($consumeActionSymptomCondition)
        ;

        $I->haveInRepository($consumeVomitingConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$moveVomitingConfig, $consumeVomitingConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        // @TODO : fix me

        // $moveAction = $I->grabService(Move::class);

        // $moveAction->loadParameters($moveActionEntity, $player, $door);
        // $moveAction->execute();

        // $I->seeInRepository(RoomLog::class, [
        //     'player' => $player,
        //     'place' => $room2,
        //     'log' => SymptomEnum::VOMITING
        // ]);

        // $consumeAction = $I->grabService(Consume::class);

        // $consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);
        // $consumeAction->execute();

        // $I->seeInRepository(RoomLog::class, [
        //     'player' => $player,
        //     'place' => $room,
        //     'log' => SymptomEnum::VOMITING
        // ]);
    }

    public function testOnPostActionFearOfCatsSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);

        $cat = new GameItem();
        $cat
            ->setName(ItemEnum::SCHRODINGER)
            ->setEquipment($itemConfig)
            ->setHolder($room)
        ;

        $I->haveInRepository($cat);

        $actionCost = new ActionCost();

        $I->haveInRepository($actionCost);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = new EquipmentConfig();
        $doorConfig
            ->setName('door')
            ->setActions(new ArrayCollection([$moveActionEntity]))
        ;
        $I->haveInRepository($doorConfig);

        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $characterConfig = $I->have(CharacterConfig::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room2,
        ]);

        $moveActionSymptomCondition = new SymptomCondition(SymptomConditionEnum::REASON);
        $moveActionSymptomCondition
            ->setCondition(ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveActionSymptomCondition);

        $catInRoomSymptomCondition = new SymptomCondition(SymptomConditionEnum::ITEM_IN_ROOM);
        $catInRoomSymptomCondition
            ->setCondition(ItemEnum::SCHRODINGER);

        $I->haveInRepository($catInRoomSymptomCondition);

        $symptomConfig = new SymptomConfig(SymptomEnum::FEAR_OF_CATS);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomCondition($moveActionSymptomCondition)
            ->addSymptomCondition($catInRoomSymptomCondition)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
        ;

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        // @TO DO : fix Call to a member function getActions() on bool error

        // $moveAction = $I->grabService(Move::class);

        // $moveAction->loadParameters($moveActionEntity, $player, $door);
        // $moveAction->execute();

        // $I->seeInRepository(RoomLog::class, [
        //     'player' => $player,
        //     'place' => $room,
        //     'log' => SymptomEnum::FEAR_OF_CATS,
        // ]);
    }
}
