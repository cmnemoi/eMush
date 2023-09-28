<?php

namespace Mush\Tests\functional\Place\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class RoomEventCest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRoomEventOnNonRoomPlace(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $time = new \DateTime();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'type' => PlaceTypeEnum::SPACE]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        $roomEvent = new RoomEvent($room, [RoomEvent::ELECTRIC_ARC], $time);

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventService->callEvent($roomEvent, RoomEvent::TREMOR);
        }
        );

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventService->callEvent($roomEvent, RoomEvent::ELECTRIC_ARC);
        }
        );
    }

    public function testNewFire(FunctionalTester $I)
    {
        $statusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::FIRE]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $time = new \DateTime();
        /** @var Place $room */
        $room = $I->have(Place::class);

        $room->setDaedalus($daedalus);

        $this->statusService->createStatusFromName(StatusEnum::FIRE, $room, [EventEnum::NEW_CYCLE, StatusEnum::FIRE], $time);

        $I->assertEquals(1, $room->getStatuses()->count());

        /** @var Status $fireStatus */
        $fireStatus = $room->getStatuses()->first();

        $I->assertEquals($room, $fireStatus->getOwner());
        $I->assertEquals(StatusEnum::FIRE, $fireStatus->getName());

        $I->seeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'channel' => $channel,
        ]);
        $I->seeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::NEW_FIRE,
            'channel' => $channel,
        ]);
    }

    public function testTremor(FunctionalTester $I)
    {
        $time = new \DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $roomWithPlayers */
        $roomWithPlayers = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY]);
        /** @var Place $roomWithoutPlayers */
        $roomWithoutPlayers = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::BRAVO_BAY]);

        $rooms = new ArrayCollection([$roomWithPlayers, $roomWithoutPlayers]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $roomWithPlayers]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        // filter rooms with players
        $rooms = $rooms->filter(function (Place $room) {
            return $room->getPlayers()->getPlayerAlive()->count() > 0;
        });

        // apply tremor on rooms with players
        $rooms->map(function (Place $room) use ($time) {
            $roomEvent = new RoomEvent($room, [EventEnum::NEW_CYCLE, RoomEvent::TREMOR], $time);
            $this->eventService->callEvent($roomEvent, RoomEvent::TREMOR);
        });

        $I->assertEquals(8, $player->getHealthPoint());
        $I->seeInRepository(RoomLog::class, [
            'place' => $roomWithPlayers->getName(),
            'log' => LogEnum::TREMOR_GRAVITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $roomWithoutPlayers->getName(),
            'log' => LogEnum::TREMOR_GRAVITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testElectricArc(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        $time = new \DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig]);
        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);
        /** @var EquipmentConfig $tabulatrix */
        $tabulatrixConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig, 'name' => EquipmentEnum::TABULATRIX]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('some other name')
        ;
        $I->haveInRepository($gameItem);

        $tabulatrix = new GameEquipment($room);
        $tabulatrix
            ->setEquipment($tabulatrixConfig)
            ->setName(EquipmentEnum::TABULATRIX)
        ;

        $I->haveInRepository($tabulatrix);

        $roomEvent = new RoomEvent($room, [EventEnum::NEW_CYCLE, RoomEvent::ELECTRIC_ARC], $time);
        $this->eventService->callEvent($roomEvent, RoomEvent::ELECTRIC_ARC);

        $I->assertEquals(7, $player->getHealthPoint());
        $I->assertTrue($gameEquipment->isBroken());
        $I->assertFalse($gameItem->isBroken());
        $I->assertTrue($tabulatrix->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => LogEnum::ELECTRIC_ARC,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->dontSeeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'channel' => $channel,
        ]);
        $I->dontSeeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::BROKEN_EQUIPMENT,
            'channel' => $channel,
        ]);
    }

    public function testEquipmentBreak(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        $time = new \DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig]);
        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['gameConfig' => $gameConfig]);
        /** @var EquipmentConfig $tabulatrix */
        $tabulatrixConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig, 'name' => EquipmentEnum::TABULATRIX]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('some other name')
        ;
        $I->haveInRepository($gameItem);

        $tabulatrix = new GameEquipment($room);
        $tabulatrix
            ->setEquipment($tabulatrixConfig)
            ->setName(EquipmentEnum::TABULATRIX)
        ;
        $I->haveInRepository($tabulatrix);

        $equipment = new ArrayCollection([$gameEquipment, $tabulatrix]);

        foreach ($equipment as $item) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::BROKEN,
                $item,
                [EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN],
                new \DateTime(),
                null,
                VisibilityEnum::PUBLIC
            );
        }

        $I->assertTrue($gameEquipment->isBroken());
        $I->assertFalse($gameItem->isBroken());
        $I->assertTrue($tabulatrix->isBroken());

        $I->seeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'channel' => $channel,
        ]);
        $I->seeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::BROKEN_EQUIPMENT,
            'channel' => $channel,
        ]);
    }

    public function testDoorBreak(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        $time = new \DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'difficultyConfig' => $difficultyConfig,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'room2']);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig]);

        $gameEquipment = new Door($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $room->addDoor($gameEquipment);
        $room2->addDoor($gameEquipment);

        $I->refreshEntities($room, $room2, $gameEquipment);

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $gameEquipment,
            [EventEnum::NEW_CYCLE, EquipmentEvent::DOOR_BROKEN],
            new \DateTime(),
            null,
            VisibilityEnum::HIDDEN
        );

        $I->assertTrue($gameEquipment->isBroken());

        $I->dontSeeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::CYCLE_FAILURES,
            'channel' => $channel,
        ]);
        $I->dontSeeInRepository(Message::class, [
            'neron' => $neron,
            'message' => NeronMessageEnum::BROKEN_EQUIPMENT,
            'channel' => $channel,
        ]);
    }
}
