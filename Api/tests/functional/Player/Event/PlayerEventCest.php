<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\User\Entity\User;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlayerEventCest
{
    private EventDispatcherInterface $eventDispatcherService;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDispatchPlayerDeath(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var Room $room */
        $greatBeyond = $I->have(Room::class, ['daedalus' => $daedalus, 'name' => RoomEnum::GREAT_BEYOND]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room, 'user' => $user]);

        $playerEvent = new PlayerEvent($player);
        $playerEvent->setReason(EndCauseEnum::CLUMSINESS);

        $this->eventDispatcherService->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

        $I->assertEquals(GameStatusEnum::FINISHED, $player->getGameStatus());
        $I->assertEquals($greatBeyond, $player->getRoom());

        $I->seeInRepository(RoomLog::class, [
            'room' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
