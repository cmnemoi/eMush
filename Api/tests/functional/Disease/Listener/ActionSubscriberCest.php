<?php

namespace Mush\Tests\functional\Disease\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Event\DiseaseEvent;
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
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
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

    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->subscriber = $I->grabService(ActionSubscriber::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
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

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);

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

        $symptomConfig = new EventModifierConfig('breakouts_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierName(SymptomEnum::BREAKOUTS);

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $I->refreshEntities($player);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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

        $takeActionEntity = new ActionConfig();
        $takeActionEntity
            ->setActionName(ActionEnum::TAKE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
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
            ->setEquipment($itemConfig);

        $I->haveInRepository($cat);

        $symptomConfig = new EventModifierConfig(SymptomEnum::CAT_ALLERGY . '_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setTagConstraints([
                ActionEnum::TAKE => ModifierRequirementEnum::ALL_TAGS,
                ItemEnum::SCHRODINGER => ModifierRequirementEnum::ALL_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::CAT_ALLERGY);
        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::CAT_ALLERGY)
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);

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

        $symptomConfig = new EventModifierConfig(SymptomEnum::DROOLING . '_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::DROOLING);

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);

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

        $symptomConfig = new EventModifierConfig(SymptomEnum::FOAMING_MOUTH . '_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setApplyOnTarget(false)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::FOAMING_MOUTH);

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);

        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);

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

        $symptomConfig = new EventModifierConfig(SymptomEnum::SNEEZING . '_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::SNEEZING);

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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
            ->buildName(GameConfigEnum::TEST);
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

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        $consumeActionEntity = new ActionConfig();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($consumeActionEntity);

        $consumeDrugActionEntity = new ActionConfig();
        $consumeDrugActionEntity
            ->setActionName(ActionEnum::CONSUME_DRUG)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($consumeDrugActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2);

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        $ration = new Ration();
        $ration
            ->setActions(new ArrayCollection([$consumeActionEntity]))
            ->setName('ration_test');
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setRation($ration);
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
            ->setEquipmentName('ration');

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration');
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

        $vomitingConfig = new EventModifierConfig(SymptomEnum::VOMITING);
        $vomitingConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setTagConstraints([
                ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS,
                ActionEnum::CONSUME => ModifierRequirementEnum::ANY_TAGS,
            ])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::VOMITING);
        $I->haveInRepository($vomitingConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$vomitingConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);

        $cat = new GameItem($room);
        $cat
            ->setName(ItemEnum::SCHRODINGER)
            ->setEquipment($itemConfig);

        $I->haveInRepository($cat);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = new EquipmentConfig();
        $doorConfig
            ->setEquipmentName('door')
            ->setName('door')
            ->setActions(new ArrayCollection([$moveActionEntity]));
        $I->haveInRepository($doorConfig);

        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);

        $I->haveInRepository($door);

        $room->addDoor($door);
        $room2->addDoor($door);

        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $characterConfig->setActionsConfig(new ArrayCollection([$moveActionEntity]));

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

        $catInRoomSymptomActivationRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, [
            'name' => 'item_in_room_schrodinger',
        ]);

        $symptomConfig = new EventModifierConfig(SymptomEnum::FEAR_OF_CATS . '_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setTagConstraints([ActionEnum::MOVE => ModifierRequirementEnum::ANY_TAGS])
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierActivationRequirements([$catInRoomSymptomActivationRequirement])
            ->setModifierName(SymptomEnum::FEAR_OF_CATS);

        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);

        $I->haveInRepository($diseaseConfig);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

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
        $symptomConfig = new EventModifierConfig('psychotic_attacks_test');
        $symptomConfig
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setApplyOnTarget(false)
            ->setModifierStrategy(ModifierStrategyEnum::SYMPTOM_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(SymptomEnum::PSYCHOTIC_ATTACKS);
        $I->haveInRepository($symptomConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('Name')
            ->setModifierConfigs([$symptomConfig])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases(['Name' => 1])
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCauseConfig);
        $diseaseCauseContactConfig = new DiseaseCauseConfig();
        $diseaseCauseContactConfig
            ->setDiseases([])
            ->setCauseName(DiseaseCauseEnum::CONTACT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCauseContactConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCauseConfig, $diseaseCauseContactConfig]),
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
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $attackAction = new ActionConfig();
        $attackAction
            ->setActionName(ActionEnum::ATTACK)
            ->setRange(ActionRangeEnum::OTHER_PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($attackAction);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        $place = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'alpha_bay']);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = new EquipmentConfig();
        $doorConfig
            ->setEquipmentName('door')
            ->setName('door')
            ->setActions(new ArrayCollection([$moveActionEntity]));
        $I->haveInRepository($doorConfig);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $place->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($place, $room2, $door);

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
            'place' => $room2,
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
            ->setName('weapon_knife_test');
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
            ->setEquipment($knifeItemConfig);
        $I->haveInRepository($knife);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseasePoint(10);
        $I->haveInRepository($playerDisease);
        $I->refreshEntities($player);

        $diseaseEvent = new DiseaseEvent($playerDisease, [], new \DateTime());
        $this->eventService->callEvent($diseaseEvent, DiseaseEvent::APPEAR_DISEASE);
        $I->assertCount(1, $player->getModifiers());

        $moveAction = $I->grabService(Move::class);
        $moveAction->loadParameters($moveActionEntity, $player, $door);
        $moveAction->execute();

        $I->seeInRepository(RoomLog::class, [
            'playerInfo' => $playerInfo,
            'place' => $room2->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => 'attack_success',
        ]);
    }
}
