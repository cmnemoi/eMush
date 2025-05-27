<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class LastPlayerCloseGameCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testLastPlayerCloseGameSimpleCase(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
    }

    public function testLastPlayerCloseGameSeveralPlayers(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, [
            'name' => 'other_character',
        ]);

        /** @var Player $player2 */
        $player2 = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig2);
        $playerInfo2->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->haveInRepository($player2);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
    }

    public function testLastPlayerCloseGameCheckStatusRemoval(FunctionalTester $I)
    {
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'statusConfigs' => new ArrayCollection([$mushConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower');
        $I->haveInRepository($gameEquipment);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $status = new Status($player, $mushConfig);
        $I->haveInRepository($status);
        $placeStatus = new Status($room, $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlaceStatusEnum::MUSH_TRAPPED->value]));
        $I->haveInRepository($placeStatus);
        $equipmentStatus = new Status($gameEquipment, $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]));
        $I->haveInRepository($equipmentStatus);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
        $I->dontSeeInRepository(Status::class);
        $I->dontSeeInRepository(StatusTarget::class);
        $I->dontSeeInRepository(GameEquipment::class);
    }

    public function testLastPlayerCloseGameCheckEquipmentRemoval(FunctionalTester $I)
    {
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower');
        $I->haveInRepository($gameEquipment);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
        $I->dontSeeInRepository(GameEquipment::class);
    }

    public function testLastPlayerCloseGameCheckEquipmentRemovalWithOwner(FunctionalTester $I)
    {
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $gameEquipment = new GameEquipment($room2);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
            ->setOwner($player);
        $I->haveInRepository($gameEquipment);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
        $I->dontSeeInRepository(GameEquipment::class);
    }

    public function testLastPlayerCloseGameCheckAlertRemoval(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $reportedAlert = new AlertElement();
        $reportedAlert->setPlace($room);
        $I->haveInRepository($reportedAlert);

        $alertFire = new Alert();
        $alertFire
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::HUNGER)
            ->addAlertElement($reportedAlert);

        $I->haveInRepository($alertFire);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
        $I->dontSeeInRepository(Alert::class);
        $I->dontSeeInRepository(AlertElement::class);
    }

    public function testLastPlayerCloseGameCheckConsumableEffect(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus);
        $I->haveInRepository($effect);

        $plantEffect = new PlantEffect();
        $plantEffect
            ->setDaedalus($daedalus);
        $I->haveInRepository($plantEffect);

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDaedalus($daedalus)
            ->setName('test');
        $I->haveInRepository($consumableDisease);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
        $I->dontSeeInRepository(ConsumableEffect::class);
        $I->dontSeeInRepository(ConsumableDisease::class);
    }

    public function testLastPlayerCloseGameCheckModifierRemoval(FunctionalTester $I)
    {
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);

        $modifierConfig = new VariableEventModifierConfig('testModifierDecreaseShowerCost1Action');
        $modifierConfig
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setTargetEvent(ActionEnum::TAKE_SHOWER->value)
            ->setModifierRange(ReachEnum::INVENTORY)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $I->haveInRepository($modifierConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::FINISHED);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower');
        $I->haveInRepository($gameEquipment);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $playerInfo->setGameStatus(GameStatusEnum::CLOSED);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->haveInRepository($player);

        $modifierPlayer = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifierPlayer);
        $modifierPlace = new GameModifier($room, $modifierConfig);
        $I->haveInRepository($modifierPlace);
        $modifierDaedalus = new GameModifier($daedalus, $modifierConfig);
        $I->haveInRepository($modifierDaedalus);
        $modifierEquipment = new GameModifier($gameEquipment, $modifierConfig);
        $I->haveInRepository($modifierEquipment);

        $event = new PlayerEvent($player, [ActionEnum::HIT->value], new \DateTime());
        $this->eventService->callEvent($event, PlayerEvent::END_PLAYER);

        $daedalusInfo = $I->grabEntityFromRepository(DaedalusInfo::class);
        $I->assertEquals(GameStatusEnum::CLOSED, $daedalusInfo->getGameStatus());
        $I->dontSeeInRepository(Daedalus::class);
        $I->seeInRepository(DaedalusInfo::class);
        $I->seeInRepository(ClosedDaedalus::class);

        $I->dontSeeInRepository(Player::class);
        $I->seeInRepository(PlayerInfo::class);
        $I->seeInRepository(ClosedPlayer::class);

        $I->dontSeeInRepository(Place::class);
        $I->dontSeeInRepository(GameModifier::class);
        $I->seeInRepository(VariableEventModifierConfig::class);
        $I->dontSeeInRepository(GameEquipment::class);
    }
}
