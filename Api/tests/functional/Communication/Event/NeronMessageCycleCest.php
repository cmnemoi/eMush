<?php

namespace Mush\Tests\Communication\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusCycleEvent;

class NeronMessageCycleCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testNewFire(FunctionalTester $I)
    {
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'propagatingFireRate' => 100,
            'hullFireDamageRate' => 0,
            ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room3 */
        $room3 = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room4 */
        $room4 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'characterConfig' => $characterConfig,
            'healthPoint' => 99,
        ]);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(StatusEnum::FIRE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($statusConfig);

        /** @var EquipmentConfig $equipmentConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['isFireBreakable' => false, 'isFireDestroyable' => false, 'gameConfig' => $gameConfig]);

        $doorConfig
            ->setGameConfig($daedalus->getGameConfig())
            ->setIsFireBreakable(false)
            ->setIsFireDestroyable(false);

        $door1 = new Door();
        $door1
            ->setName('door name')
            ->setEquipment($doorConfig);

        $room->addDoor($door1);
        $room2->addDoor($door1);

        $door2 = new Door();
        $door2
            ->setName('door name')
            ->setEquipment($doorConfig);

        $room->addDoor($door2);
        $room3->addDoor($door2);

        $door3 = new Door();
        $door3
            ->setName('door name')
            ->setEquipment($doorConfig);

        $room->addDoor($door3);
        $room4->addDoor($door3);

        $time = new DateTime();
        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setName(StatusEnum::FIRE);
        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(1);

        $room->addStatus($status);

        $cycleEvent = new StatusCycleEvent($status, $room, EventEnum::NEW_CYCLE, $time);

        $I->haveInRepository($status);
        $I->refreshEntities($player, $daedalus);

        $this->eventService->callEvent($cycleEvent, StatusCycleEvent::STATUS_NEW_CYCLE);

        $message = $I->grabEntityFromRepository(Message::class, ['message' => NeronMessageEnum::CYCLE_FAILURES]);
        $fireMessages = $channel->getMessages()->filter(fn (Message $message) => $message->getMessage() === NeronMessageEnum::NEW_FIRE);

        $I->assertCount(4, $channel->getMessages());
        $I->assertCount(3, $fireMessages);
        $I->assertEquals($fireMessages->first()->getParent(), $message);
    }
}
