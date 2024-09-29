<?php

namespace Mush\Tests\functional\Communication\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

final class PrivateChannelAuthorizationCest
{
    private Drop $dropAction;
    private Move $moveAction;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        $this->dropAction = $I->grabService(Drop::class);
        $this->moveAction = $I->grabService(Move::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function testDropTalkie(FunctionalTester $I)
    {
        $dropActionEntity = new ActionConfig();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dropActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
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

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room2,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2);
        $I->haveInRepository($channelPlayer2);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActionConfigs(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player);
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2);
        $I->haveInRepository($talkie2);

        $this->dropAction->loadParameters($dropActionEntity, $talkie2, $player2, $talkie2);
        $this->dropAction->execute();

        $I->dontSeeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo2->getId(),
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    public function testDropTalkieCanWisperMove(FunctionalTester $I)
    {
        $dropActionEntity = new ActionConfig();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dropActionEntity);
        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => ProjectName::ICARUS_LARGER_BAY]);
        $project = new Project($projectConfig, $daedalus);
        $I->haveInRepository($project);
        $daedalus->addProject($project);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
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

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2);
        $I->haveInRepository($channelPlayer2);

        // add a door
        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, [
            'name' => 'door_test',
            'actionConfigs' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActionConfigs(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player);
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2);
        $I->haveInRepository($talkie2);

        $this->dropAction->loadParameters($dropActionEntity, $talkie2, $player2, $talkie2);
        $this->dropAction->execute();

        $I->assertCount(2, $privateChannel->getParticipants());
        $I->seeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo2->getId(),
        ]);
        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);

        $this->moveAction->loadParameters($moveActionEntity, $door, $player2, $door);
        $this->moveAction->execute();

        $I->dontSeeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo2->getId(),
        ]);
        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    public function testDropTalkieCanWisperOtherPlayerMove(FunctionalTester $I)
    {
        $dropActionEntity = new ActionConfig();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dropActionEntity);
        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => ProjectName::ICARUS_LARGER_BAY]);
        $project = new Project($projectConfig, $daedalus);
        $I->haveInRepository($project);
        $daedalus->addProject($project);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
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

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2);
        $I->haveInRepository($channelPlayer2);

        // add a door
        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, [
            'name' => 'door_test',
            'actionConfigs' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActionConfigs(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player);
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2);
        $I->haveInRepository($talkie2);

        $this->dropAction->loadParameters($dropActionEntity, $talkie2, $player2, $talkie2);
        $this->dropAction->execute();

        $I->seeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo2->getId(),
        ]);

        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);

        $this->moveAction->loadParameters($moveActionEntity, $door, $player, $door);
        $this->moveAction->execute();

        $I->dontSeeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo2->getId(),
        ]);
        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    public function testDropTalkieThenDie(FunctionalTester $I)
    {
        $dropActionEntity = new ActionConfig();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dropActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);
        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
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

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room2,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2);
        $I->haveInRepository($channelPlayer2);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActionConfigs(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player);
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2);
        $I->haveInRepository($talkie2);

        $this->dropAction->loadParameters($dropActionEntity, $talkie2, $player2, $talkie2);
        $this->dropAction->execute();

        $I->assertCount(1, $privateChannel->getParticipants());
        $I->dontSeeInRepository(ChannelPlayer::class, [
            'channel' => $privateChannel->getId(),
            'participant' => $playerInfo2->getId(),
        ]);
        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);

        $this->playerService->killPlayer(
            player: $player2,
            endReason: EndCauseEnum::BLED,
            time: new \DateTime(),
        );

        $I->assertCount(1, $privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);
    }

    public function testDieThenDropTalkie(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCause);

        $dropActionEntity = new ActionConfig();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dropActionEntity);
        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
        ]);
        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => ProjectName::ICARUS_LARGER_BAY]);
        $project = new Project($projectConfig, $daedalus);
        $I->haveInRepository($project);
        $daedalus->addProject($project);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
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

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        // add a door
        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, [
            'name' => 'door_test',
            'actionConfigs' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE);
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo);
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2);
        $I->haveInRepository($channelPlayer2);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActionConfigs(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player);
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2);
        $I->haveInRepository($talkie2);

        // start test
        $this->dropAction->loadParameters($dropActionEntity, $talkie1, $player, $talkie1);
        $this->dropAction->execute();

        $I->assertCount(2, $privateChannel->getParticipants());

        $this->playerService->killPlayer(
            player: $player2,
            endReason: EndCauseEnum::BLED,
            time: new \DateTime(),
        );

        $I->assertCount(1, $privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        $this->moveAction->loadParameters($dropActionEntity, $door, $player, $door);
        $this->moveAction->execute();
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }
}
