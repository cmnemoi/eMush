<?php

namespace Mush\Tests\Place\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
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
use Mush\Status\Event\StatusEvent;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RoomEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testRoomEventOnNonRoomPlace(FunctionalTester $I)
    {
        $time = new \DateTime();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'type' => PlaceTypeEnum::SPACE]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'healthPoint' => 10]);

        $roomEvent = new RoomEvent($room, RoomEvent::ELECTRIC_ARC, $time);

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }
        );

        $I->expectThrowable(\LogicException::class, function () use ($roomEvent) {
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);
        }
        );
    }

    public function testNewFire(FunctionalTester $I)
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(StatusEnum::FIRE)
        ;
        $I->haveInRepository($statusConfig);

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

        $statusEvent = new StatusEvent(StatusEnum::FIRE, $room, EventEnum::NEW_CYCLE, $time);
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        $I->assertEquals(1, $room->getStatuses()->count());

        /** @var Status $fireStatus */
        $fireStatus = $room->getStatuses()->first();

        $I->assertEquals($room, $fireStatus->getOwner());
        $I->assertEquals(StatusEnum::FIRE, $fireStatus->getName());
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
        $roomWithPlayers = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $roomWithoutPlayers */
        $roomWithoutPlayers = $I->have(Place::class, ['daedalus' => $daedalus]);

        $rooms = new ArrayCollection([$roomWithPlayers, $roomWithoutPlayers]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $roomWithPlayers, 'healthPoint' => 10]);
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
            $roomEvent = new RoomEvent($room, EventEnum::NEW_CYCLE, $time);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        });

        $I->assertEquals(8, $player->getHealthPoint());
        $I->seeInRepository(RoomLog::class, [
            'place' => $roomWithPlayers->getId(),
            'log' => LogEnum::TREMOR_GRAVITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $roomWithoutPlayers->getId(),
            'log' => LogEnum::TREMOR_GRAVITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testElectricArc(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'healthPoint' => 10, 'characterConfig' => $characterConfig]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $roomEvent = new RoomEvent($room, EventEnum::NEW_CYCLE, $time);
        $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);

        $I->assertEquals(7, $player->getHealthPoint());
        $I->assertTrue($gameEquipment->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => LogEnum::ELECTRIC_ARC,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
