<?php

namespace Mush\Tests\functional\Disease\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Listener\ActionSubscriber;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ActionSubscriberCest
{
    private ActionSubscriber $subscriber;

    public function _before(FunctionalTester $I)
    {
        $this->subscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionBreakoutsSymptom(FunctionalTester $I)
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class, ['name' => 'test']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $moveActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_move',
        ]);
        $moveActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($moveActionSymptomActivationRequirement);

        $symptomConfig = new SymptomConfig('breakouts');
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
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
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::BREAKOUTS,
        ]);
    }

    public function testOnPostActionCatAllergySymptom(FunctionalTester $I)
    {
        $quincksOedema = $I->grabEntityFromRepository(DiseaseConfig::class, [
            'diseaseName' => 'quincks_oedema',
        ]);
        $burntArms = $I->grabEntityFromRepository(DiseaseConfig::class, [
            'diseaseName' => 'burnt_arms',
        ]);
        $burntHand = $I->grabEntityFromRepository(DiseaseConfig::class, [
            'diseaseName' => 'burnt_hand',
        ]);

        $catAllergyDiseaseCauseConfig = $I->grabEntityFromRepository(DiseaseCauseConfig::class, [
            'causeName' => DiseaseEnum::CAT_ALLERGY,
        ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'diseaseConfig' => new ArrayCollection([$quincksOedema, $burntArms, $burntHand]),
            'diseaseCauseConfig' => new ArrayCollection([$catAllergyDiseaseCauseConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setActionName(ActionEnum::TAKE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($takeActionEntity);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$takeActionEntity])]);

        $cat = new GameItem($room);
        $cat
            ->setName(ItemEnum::SCHRODINGER)
            ->setEquipment($itemConfig)
        ;

        $I->haveInRepository($cat);

        $takeActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_take',
        ]);
        $takeActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($takeActionSymptomActivationRequirement);

        $holdCatSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'player_equipment_schrodinger',
        ]);

        $symptomConfig = new SymptomConfig(SymptomEnum::CAT_ALLERGY);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($takeActionSymptomActivationRequirement)
            ->addSymptomActivationRequirement($holdCatSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::CAT_ALLERGY)
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
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

        $takeAction = $I->grabService(Take::class);

        $takeAction->loadParameters($takeActionEntity, $player, $cat);
        $takeAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo->getId(),
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::CAT_ALLERGY,
        ]);
    }

    public function testOnPostActionDroolingSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $moveActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_move',
        ]);
        $moveActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($moveActionSymptomActivationRequirement);

        $symptomConfig = new SymptomConfig(SymptomEnum::DROOLING);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
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
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::DROOLING,
        ]);
    }

    public function testOnPostActionFoamingMouthSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $moveActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_move',
        ]);
        $moveActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($moveActionSymptomActivationRequirement);

        $symptomConfig = new SymptomConfig(SymptomEnum::FOAMING_MOUTH);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
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
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::FOAMING_MOUTH,
        ]);
    }

    public function testOnPostActionSneezingSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $moveActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_move',
        ]);
        $moveActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($moveActionSymptomActivationRequirement);

        $symptomConfig = new SymptomConfig(SymptomEnum::SNEEZING);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
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
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::SNEEZING,
        ]);
    }

    public function testOnPostActionVomitingSymptom(FunctionalTester $I)
    {
        $dirtyStatusConfig = new StatusConfig();
        $dirtyStatusConfig
            ->setStatusName('dirty')
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($dirtyStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$dirtyStatusConfig])]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($consumeActionEntity);

        $consumeDrugActionEntity = new Action();
        $consumeDrugActionEntity
            ->setActionName(ActionEnum::CONSUME_DRUG)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($consumeDrugActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
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
        $ration
            ->setActions(new ArrayCollection([$consumeActionEntity]))
            ->setName('ration_test')
        ;
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
            'equipmentName' => 'ration',
        ]);

        $equipmentConfig
            ->setMechanics(new ArrayCollection([$ration]))
            ->setEquipmentName('ration')
        ;

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$moveActionEntity])]);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $moveActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_move',
        ]);
        $moveActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($moveActionSymptomActivationRequirement);

        $consumeActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_consume',
        ]);

        $moveVomitingConfig = new SymptomConfig(SymptomEnum::VOMITING);
        $moveVomitingConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST, ActionEnum::MOVE)
        ;

        $I->haveInRepository($moveVomitingConfig);

        $consumeVomitingConfig = new SymptomConfig(SymptomEnum::VOMITING);
        $consumeVomitingConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($consumeActionSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST, ActionEnum::CONSUME)
        ;

        $I->haveInRepository($consumeVomitingConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$moveVomitingConfig, $consumeVomitingConfig]))
            ->buildName(GameConfigEnum::TEST)
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
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::VOMITING,
        ]);

        $consumeAction = $I->grabService(Consume::class);

        $consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);
        $consumeAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::VOMITING,
        ]);
    }

    public function testOnPostActionFearOfCatsSymptom(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);

        $cat = new GameItem($room);
        $cat
            ->setName(ItemEnum::SCHRODINGER)
            ->setEquipment($itemConfig)
        ;

        $I->haveInRepository($cat);

        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = new EquipmentConfig();
        $doorConfig
            ->setEquipmentName('door')
            ->setName('door')
            ->setActions(new ArrayCollection([$moveActionEntity]))
        ;
        $I->haveInRepository($doorConfig);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $characterConfig->setActions(new ArrayCollection([$moveActionEntity]));
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room2,
        ]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $moveActionSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'reason_move',
        ]);
        $moveActionSymptomActivationRequirement->setValue(100);
        $I->refreshEntities($moveActionSymptomActivationRequirement);

        $catInRoomSymptomActivationRequirement = $I->grabEntityFromRepository(SymptomActivationRequirement::class, [
            'name' => 'item_in_room_schrodinger',
        ]);

        $symptomConfig = new SymptomConfig(SymptomEnum::FEAR_OF_CATS);
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->addSymptomActivationRequirement($moveActionSymptomActivationRequirement)
            ->addSymptomActivationRequirement($catInRoomSymptomActivationRequirement)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
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
            'playerInfo' => $playerInfo,
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => SymptomEnum::FEAR_OF_CATS,
        ]);
    }

    public function testPostActionPsychoticAttackSymptom(FunctionalTester $I)
    {
        $symptomConfig = new SymptomConfig('psychotic_attacks');
        $symptomConfig
            ->setTrigger(ActionEvent::POST_ACTION)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]))
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases(['Name' => 1])
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCauseConfig]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        $attackAction = new Action();
        $attackAction
            ->setActionName(ActionEnum::ATTACK)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->buildName(GameConfigEnum::TEST)
        ;

        $searchAction = new Action();
        $searchAction
            ->setActionName(ActionEnum::SEARCH)
            ->setScope(ActionScopeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($attackAction);
        $I->haveInRepository($searchAction);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var CharacterConfig $otherCharacterConfig */
        $otherCharacterConfig = $I->have(CharacterConfig::class, ['name' => 'test2']);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player->setHealthPoint(14);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $otherPlayer */
        $otherPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $place,
        ]);
        $otherPlayer->setPlayerVariables($characterConfig);
        $otherPlayer->setHealthPoint(1);
        $otherPlayerInfo = new PlayerInfo($otherPlayer, $user, $otherCharacterConfig);

        $I->haveInRepository($otherPlayerInfo);
        $otherPlayer->setPlayerInfo($otherPlayerInfo);
        $I->refreshEntities($otherPlayer);

        $knifeMechanic = new Weapon();
        $knifeMechanic
            ->setBaseDamageRange([1 => 100])
            ->setActions(new ArrayCollection([$attackAction]))
            ->setName('weapon_knife_test')
        ;
        $I->haveInRepository($knifeMechanic);

        /** @var ItemConfig $knifeItemConfig */
        $knifeItemConfig = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'name' => ItemEnum::KNIFE,
            'mechanics' => new ArrayCollection([$knifeMechanic]),
        ]);

        $knife = new GameItem($player);
        $knife
            ->setName(ItemEnum::KNIFE)
            ->setEquipment($knifeItemConfig)
        ;
        $I->haveInRepository($knife);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10)
        ;

        $I->haveInRepository($playerDisease);

        $I->refreshEntities($player);

        $event = new ActionEvent($searchAction, $player, $otherPlayer);

        $this->subscriber->onPostAction($event);

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo,
            'place' => $place->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => 'attack_success',
        ]);
    }
}
