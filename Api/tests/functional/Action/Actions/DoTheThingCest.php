<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\MushMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
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
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class DoTheThingCest extends AbstractFunctionalTest
{
    private ActionConfig $doTheThingConfig;
    private DoTheThing $doTheThingAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->doTheThingConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DO_THE_THING]);
        $this->doTheThingAction = $I->grabService(DoTheThing::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
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
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($didTheThingStatus);
        $pregnantStatus = new StatusConfig();
        $pregnantStatus
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($pregnantStatus);
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('disease')
            ->buildName(GameConfigEnum::TEST)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setCauseName('sex')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST);
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

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST)
            ->setOutputQuantity(2);
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($maleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6);
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
            ->setMoralPoint(6);
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
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($gameEquipment);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters(
            actionConfig: $action,
            actionProvider: $player,
            player: $player,
            target: $targetPlayer
        );

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
            $this->doTheThingAction->getActionConfig()->getActionTags(),
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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6);
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
            ->setMoralPoint(6);
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
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($gameEquipment);

        $this->doTheThingAction->loadParameters(
            actionConfig: $action,
            actionProvider: $player,
            player: $player,
            target: $targetPlayer
        );

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(
            ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED,
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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6);

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
            ->setMoralPoint(6);
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
            ->setEquipment($equipmentConfig);
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

        $this->doTheThingAction->loadParameters(
            actionConfig: $action,
            actionProvider: $player,
            player: $player,
            target: $targetPlayer
        );

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertEquals(
            ActionImpossibleCauseEnum::DO_THE_THING_WITNESS,
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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6);
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
            ->setMoralPoint(6);
        $I->flushToDatabase($targetPlayer);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $maleCharacterConfig);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $targetPlayer->setFlirts(new ArrayCollection([$player]));

        $this->doTheThingAction->loadParameters(
            actionConfig: $action,
            actionProvider: $player,
            player: $player,
            target: $targetPlayer
        );

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
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($didTheThingStatus);
        $pregnantStatus = new StatusConfig();
        $pregnantStatus
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($pregnantStatus);
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName('disease')
            ->buildName(GameConfigEnum::TEST)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);
        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setCauseName('sex')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCauseConfig);

        $diseaseCauseConfig2 = new DiseaseCauseConfig();
        $diseaseCauseConfig2
            ->setCauseName('infection')
            ->setDiseases(['disease'])
            ->buildName(GameConfigEnum::TEST);
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
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::PAOLA . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::PAOLA,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);

        $sporesStatusConfig = new ChargeStatusConfig();
        $sporesStatusConfig
            ->setStatusName(PlayerStatusEnum::SPORES)
            ->buildName(GameConfigEnum::TEST);
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
            ->setSpores(1);
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
            ->setSpores(0);
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
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($gameEquipment);

        $humanPlayer->setFlirts(new ArrayCollection([$mushPlayer]));

        $this->doTheThingAction->loadParameters(
            actionConfig: $action,
            actionProvider: $mushPlayer,
            player: $mushPlayer,
            target: $humanPlayer
        );

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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $femaleCharacterConfig */
        $femaleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::CHUN,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var CharacterConfig $maleCharacterConfig */
        $maleCharacterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::DEREK,
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($femaleCharacterConfig);
        $player
            ->setActionPoint(10)
            ->setMoralPoint(6);

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
            ->setMoralPoint(6);
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
            ->setEquipment($equipmentConfig);
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

        $this->doTheThingAction->loadParameters(
            actionConfig: $action,
            actionProvider: $player,
            player: $player,
            target: $targetPlayer
        );

        $I->assertTrue($this->doTheThingAction->isVisible());
        $I->assertNull($this->doTheThingAction->cannotExecuteReason());
    }

    public function testDoTheThingNotVisibleIfSofaIsBroken(FunctionalTester $I): void
    {
        // given there is chun and kuan ti in the laboratory
        $laboratory = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);
        $chun = $this->player1;
        $kuanTi = $this->player2;

        // given kuan ti has flirted with chun
        $kuanTi->setFlirts(new ArrayCollection([$chun]));

        // given there is a sofa in the room
        $sofaConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SWEDISH_SOFA]);
        $sofa = new GameEquipment($laboratory);
        $sofa
            ->setName(EquipmentEnum::SWEDISH_SOFA)
            ->setEquipment($sofaConfig);
        $I->haveInRepository($sofa);

        // given the sofa is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $sofa,
            tags: [],
            time: new \DateTime(),
        );

        // when chun tries to do the thing with kuan ti
        $this->doTheThingAction->loadParameters(
            actionConfig: $this->doTheThingConfig,
            actionProvider: $chun,
            player: $chun,
            target: $kuanTi,
        );

        // then the action is not visible
        $I->assertFalse($this->doTheThingAction->isVisible());
    }

    public function testImmunizedPlayerIsNotInfectedWhileDoingItWithAMushPlayer(FunctionalTester $I): void
    {
        // given I have an immunized player
        $immunizedPlayer = $this->player1;
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::IMMUNIZED,
            holder: $immunizedPlayer,
            tags: [],
            time: new \DateTime(),
        );

        // given I have a mush player
        $mushPlayer = $this->player2;
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $mushPlayer,
            tags: [],
            time: new \DateTime(),
        );

        // given this Mush player has a spore to transmit
        $mushPlayer->setSpores(1);

        // given there is a sofa in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $immunizedPlayer->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given players have flirted with each other
        $immunizedPlayer->setFlirts(new ArrayCollection([$mushPlayer]));
        $mushPlayer->setFlirts(new ArrayCollection([$immunizedPlayer]));

        // when the immunized player does the thing with the mush player
        $this->doTheThingAction->loadParameters(
            actionConfig: $this->doTheThingConfig,
            actionProvider: $immunizedPlayer,
            player: $immunizedPlayer,
            target: $mushPlayer,
        );
        $this->doTheThingAction->execute();

        // then the immunized player is not infected
        $I->assertEquals(0, $immunizedPlayer->getSpores());

        // then I should not see a message in Mush channel about the immunized player being infected
        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'message' => MushMessageEnum::INFECT_STD,
            ],
        );
    }
}
