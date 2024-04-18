<?php

namespace Mush\Tests\functional\Communication\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class NeronMessageCycleCest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testNewFire(FunctionalTester $I): void
    {
        $statusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::FIRE]);

        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'propagatingFireRate' => 100,
            'hullFireDamageRate' => 0,
            'maximumAllowedSpreadingFires' => 2,
        ]);

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
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
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

        /** @var Place $room3 */
        $room3 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room4 */
        $room4 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $characterConfig
            ->setMaxHealthPoint(99)
            ->setInitHealthPoint(99);
        $I->refreshEntities($characterConfig);

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

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['isFireBreakable' => false, 'isFireDestroyable' => false, 'gameConfig' => $gameConfig]);

        $doorConfig
            ->setIsFireBreakable(false)
            ->setIsFireDestroyable(false);

        $door1 = new Door($room);
        $door1
            ->setName('door name')
            ->setEquipment($doorConfig);

        $room->addDoor($door1);
        $room2->addDoor($door1);

        $door2 = new Door($room);
        $door2
            ->setName('door name')
            ->setEquipment($doorConfig);

        $room->addDoor($door2);
        $room3->addDoor($door2);

        $door3 = new Door($room4);
        $door3
            ->setName('door name')
            ->setEquipment($doorConfig);

        $room->addDoor($door3);
        $room4->addDoor($door3);

        $time = new \DateTime();
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(StatusEnum::FIRE)
            ->setName('otherFireStatus');
        $I->haveInRepository($statusConfig);

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $room,
            [],
            new \DateTime()
        );
        $status->setCharge(1);

        $I->assertCount(2, $channel->getMessages());

        $cycleEvent = new StatusCycleEvent($status, $room, [EventEnum::NEW_CYCLE], $time);

        $I->refreshEntities($player, $daedalus);

        $this->eventService->callEvent($cycleEvent, StatusCycleEvent::STATUS_NEW_CYCLE);

        $message = $I->grabEntityFromRepository(Message::class, ['message' => NeronMessageEnum::CYCLE_FAILURES]);

        $I->refreshEntities($channel);
        $fireMessages = $channel->getMessages()->filter(static fn (Message $message) => $message->getMessage() === NeronMessageEnum::NEW_FIRE);
        $I->assertCount(3, $channel->getMessages());
        $I->assertCount(2, $fireMessages);
        $I->assertEquals($fireMessages->first()->getParent(), $message);
    }
}
