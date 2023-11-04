<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class DoTheThingCest
{
    private DoTheThing $doTheThingAction;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->doTheThingAction = $I->grabService(DoTheThing::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testDoTheThing(FunctionalTester $I)
    {
        $didTheThingStatus = new ChargeStatusConfig();
        $didTheThingStatus
            ->setStatusName(PlayerStatusEnum::DID_THE_THING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($didTheThingStatus);
        $pregnantStatus = new StatusConfig();
        $pregnantStatus
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($pregnantStatus);
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('disease')
            ->buildName(GameConfigEnum::TEST)
                ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setCauseName('sex')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $infectionDiseaseCauseConfig = $I->grabEntityFromRepository(DiseaseCauseConfig::class, ['causeName' => 'infection']);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
            ->setOutputQuantity(2)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($maleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $I->flushToDatabase($player);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($maleCharacterConfig);
        $targetPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $I->flushToDatabase($targetPlayer);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertNull($this->doTheThingAction->cannotExecuteReason());

        $this->doTheThingAction->execute();

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(8, $player->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::DO_THE_THING_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // Check if pregnancy log works
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::PREGNANT,
            $player,
            $this->doTheThingAction->getAction()->getActionTags(),
            new \DateTime(),
            null,
            VisibilityEnum::PRIVATE
        );

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => StatusEventLogEnum::BECOME_PREGNANT,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testNoFlirt(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $I->flushToDatabase($player);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($maleCharacterConfig);
        $targetPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $I->flushToDatabase($targetPlayer);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED,
            $this->doTheThingAction->cannotExecuteReason()
        );
    }

    public function testWitness(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($maleCharacterConfig);
        $targetPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        /** @var Player $witnessPlayer */
        $witnessPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $witnessPlayerInfo = new PlayerInfo($witnessPlayer, $user, $maleCharacterConfig);
        $witnessPlayerInfo->setGameStatus(GameStatusEnum::CURRENT);

        $I->haveInRepository($witnessPlayerInfo);
        $witnessPlayer->setPlayerInfo($witnessPlayerInfo);
        $I->refreshEntities($witnessPlayer);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(ActionImpossibleCauseEnum::DO_THE_THING_WITNESS,
            $this->doTheThingAction->cannotExecuteReason()
        );
    }

    public function testRoomHasBed(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $I->flushToDatabase($player);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($maleCharacterConfig);
        $targetPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $I->flushToDatabase($targetPlayer);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->doTheThingAction->isVisible());
    }

    public function testSporesTransmission(FunctionalTester $I)
    {
        $didTheThingStatus = new ChargeStatusConfig();
        $didTheThingStatus
            ->setStatusName(PlayerStatusEnum::DID_THE_THING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($didTheThingStatus);
        $pregnantStatus = new StatusConfig();
        $pregnantStatus
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($pregnantStatus);
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('disease')
            ->buildName(GameConfigEnum::TEST)
                ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setCauseName('sex')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $diseaseCauseConfig2 = new DiseaseCauseConfig();
        $diseaseCauseConfig2
            ->setCauseName('infection')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH)
        ;
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::PAOLA . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::PAOLA,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushConfig);

        $sporesStatusConfig = new ChargeStatusConfig();
        $sporesStatusConfig
            ->setStatusName(PlayerStatusEnum::SPORES)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($sporesStatusConfig);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);

        $mushStatus = new ChargeStatus($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        $mushPlayer->setPlayerVariables($maleCharacterConfig);
        $mushPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
            ->setSpores(1)
        ;
        $I->flushToDatabase($mushPlayer);
        /** @var User $user */
        $user = $I->have(User::class);
        $mushPlayerInfo = new PlayerInfo($mushPlayer, $user, $femaleCharacterConfig);

        $I->haveInRepository($mushPlayerInfo);
        $mushPlayer->setPlayerInfo($mushPlayerInfo);
        $I->refreshEntities($mushPlayer);

        /** @var Player $humanPlayer */
        $humanPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $humanPlayer->setPlayerVariables($maleCharacterConfig);
        $humanPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
            ->setSpores(0)
        ;
        $I->flushToDatabase($humanPlayer);
        $humanPlayerInfo = new PlayerInfo($humanPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($humanPlayerInfo);
        $humanPlayer->setPlayerInfo($humanPlayerInfo);
        $I->refreshEntities($humanPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $humanPlayer->setFlirts(new ArrayCollection([$mushPlayer]));

        $this->doTheThingAction->loadParameters($action, $mushPlayer, $humanPlayer);

        $this->doTheThingAction->execute();

        $I->refreshEntities([$humanPlayer, $mushPlayer]);

        $I->assertEquals(1, $humanPlayer->getSpores());
        $I->assertEquals(0, $mushPlayer->getSpores());
    }

    public function testDeadWitness(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $femaleCharacterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($maleCharacterConfig);
        $targetPlayer
            ->setActionPoint(10)
            ->setMoralPoint(6)
        ;
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($equipmentConfig)
        ;
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        /** @var Player $deadWitnessPlayer */
        $deadWitnessPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $deadWitnessPlayerInfo = new PlayerInfo($deadWitnessPlayer, $user, $maleCharacterConfig);
        $deadWitnessPlayerInfo->setGameStatus(GameStatusEnum::CLOSED);

        $I->haveInRepository($deadWitnessPlayerInfo);
        $deadWitnessPlayer->setPlayerInfo($deadWitnessPlayerInfo);
        $I->refreshEntities($deadWitnessPlayer);

        $this->doTheThingAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertNull($this->doTheThingAction->cannotExecuteReason());
    }
}
