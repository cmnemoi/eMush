<?php

namespace Mush\Tests\functional\Communication\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\ScrewTalkie;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
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
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PiratedPrivateChannelParticipantsCest
{
    private Drop $dropAction;
    private Move $moveAction;
    private ScrewTalkie $pirateAction;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->dropAction = $I->grabService(Drop::class);
        $this->pirateAction = $I->grabService(ScrewTalkie::class);
        $this->moveAction = $I->grabService(Move::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testPirateThenDieThenDropTalkie(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig
            ->setStatusName(PlayerStatusEnum::TALKIE_SCREWED)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($piratedStatusConfig);
        $mushStatusConfig = new StatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);
        $brokenStatusConfig = new StatusConfig();
        $brokenStatusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($brokenStatusConfig);

        $dropActionEntity = new Action();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($dropActionEntity);
        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        $pirateActionEntity = new Action();
        $pirateActionEntity
            ->setActionName(ActionEnum::SCREW_TALKIE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($pirateActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$brokenStatusConfig, $piratedStatusConfig, $mushStatusConfig]),
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

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$pirateActionEntity])]);
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

        $status = new Status($player, $mushStatusConfig);
        $I->haveInRepository($status);

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
            'actions' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo)
        ;
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2)
        ;
        $I->haveInRepository($channelPlayer2);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($publicChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($mushChannel);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActions(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player)
        ;
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2)
        ;
        $I->haveInRepository($talkie2);

        // start test: player pirate player2
        $this->pirateAction->loadParameters($pirateActionEntity, $player, $player2);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();
        $I->assertCount(2, $player->getStatuses());
        $I->assertCount(2, $privateChannel->getParticipants());

        // player die
        $playerEvent = new PlayerEvent(
            $player,
            [EndCauseEnum::BLED],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        // player2 drop its talkie
        $this->dropAction->loadParameters($dropActionEntity, $player2, $talkie2);
        $this->dropAction->execute();
        $I->assertCount(1, $privateChannel->getParticipants());

        // player 2 change room
        $this->moveAction->loadParameters($dropActionEntity, $player2, $door);
        $this->moveAction->execute();
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }

    public function testPirate(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig
            ->setStatusName(PlayerStatusEnum::TALKIE_SCREWED)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($piratedStatusConfig);
        $mushStatusConfig = new StatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);
        $brokenStatusConfig = new StatusConfig();
        $brokenStatusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($brokenStatusConfig);

        $dropActionEntity = new Action();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($dropActionEntity);
        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        $pirateActionEntity = new Action();
        $pirateActionEntity
            ->setActionName(ActionEnum::SCREW_TALKIE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($pirateActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$brokenStatusConfig, $piratedStatusConfig, $mushStatusConfig]),
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

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$pirateActionEntity])]);
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

        /** @var Player $player3 */
        $player3 = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player3->setPlayerVariables($characterConfig);
        $playerInfo3 = new PlayerInfo($player3, $user, $characterConfig);
        $I->haveInRepository($playerInfo3);
        $player3->setPlayerInfo($playerInfo3);
        $I->refreshEntities($player3);

        $status = new Status($player3, $mushStatusConfig);
        $I->haveInRepository($status);

        // add a door
        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, [
            'name' => 'door_test',
            'actions' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo)
        ;
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2)
        ;
        $I->haveInRepository($channelPlayer2);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($publicChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($mushChannel);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActions(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player)
        ;
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2)
        ;
        $I->haveInRepository($talkie2);

        // start test: player3 pirate player
        // event if player  cannot have access to the private channel, it is not kicked so player still have access
        $this->pirateAction->loadParameters($pirateActionEntity, $player3, $player);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();
        $I->assertCount(2, $player3->getStatuses());
        $I->assertCount(2, $privateChannel->getParticipants());
    }

    public function testPirateThenMoveThenDieThenDropTalkie(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        $piratedStatusConfig = new StatusConfig();
        $piratedStatusConfig
            ->setStatusName(PlayerStatusEnum::TALKIE_SCREWED)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($piratedStatusConfig);
        $mushStatusConfig = new StatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);
        $brokenStatusConfig = new StatusConfig();
        $brokenStatusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($brokenStatusConfig);

        $dropActionEntity = new Action();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($dropActionEntity);
        $moveActionEntity = new Action();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($moveActionEntity);

        $pirateActionEntity = new Action();
        $pirateActionEntity
            ->setActionName(ActionEnum::SCREW_TALKIE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($pirateActionEntity);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$brokenStatusConfig, $piratedStatusConfig, $mushStatusConfig]),
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

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        // Create players
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$pirateActionEntity])]);
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

        $status = new Status($player, $mushStatusConfig);
        $I->haveInRepository($status);

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
            'actions' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
        ;
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        // create privateChannel
        $privateChannel = new Channel();
        $privateChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PRIVATE)
        ;
        $I->haveInRepository($privateChannel);

        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo)
        ;
        $I->haveInRepository($channelPlayer);

        $channelPlayer2 = new ChannelPlayer();
        $channelPlayer2
            ->setChannel($privateChannel)
            ->setParticipant($playerInfo2)
        ;
        $I->haveInRepository($channelPlayer2);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($publicChannel);

        $mushChannel = new Channel();
        $mushChannel
            ->setScope(ChannelScopeEnum::MUSH)
            ->setDaedalus($daedalusInfo)
        ;
        $I->haveInRepository($mushChannel);

        // initialize talkies
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);
        $equipmentConfig->setActions(new ArrayCollection([$dropActionEntity]));

        $talkie1 = new GameItem($player);
        $talkie1
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player)
        ;
        $I->haveInRepository($talkie1);

        $talkie2 = new GameItem($player2);
        $talkie2
            ->setEquipment($equipmentConfig)
            ->setName(ItemEnum::WALKIE_TALKIE)
            ->setOwner($player2)
        ;
        $I->haveInRepository($talkie2);

        // start test: player pirate player2
        $this->pirateAction->loadParameters($pirateActionEntity, $player, $player2);
        $I->assertTrue($this->pirateAction->isVisible());
        $this->pirateAction->execute();
        $I->assertCount(2, $player->getStatuses());
        $I->assertCount(2, $privateChannel->getParticipants());

        // player2 move: even if he does not have access to privateChannel, he is not kicked because of screwed talkie
        $this->moveAction->loadParameters($dropActionEntity, $player2, $door);
        $this->moveAction->execute();
        $I->assertCount(2, $privateChannel->getParticipants());

        // player join player
        $this->moveAction->loadParameters($dropActionEntity, $player, $door);
        $this->moveAction->execute();
        $I->assertCount(2, $privateChannel->getParticipants());

        // player die
        $playerEvent = new PlayerEvent(
            $player,
            [EndCauseEnum::BLED],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->seeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
        ]);

        // player2 drop its talkie
        $this->dropAction->loadParameters($dropActionEntity, $player2, $talkie2);
        $this->dropAction->execute();
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);

        // player 2 change room
        $this->moveAction->loadParameters($dropActionEntity, $player2, $door);
        $this->moveAction->execute();
        $I->assertCount(1, $privateChannel->getParticipants());
        $I->dontSeeInRepository(Message::class, [
            'channel' => $privateChannel->getId(),
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_TALKY,
        ]);
    }
}
