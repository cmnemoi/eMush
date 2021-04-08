<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
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
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room */
        $greatBeyond = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::GREAT_BEYOND]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'user' => $user, 'characterConfig' => $characterConfig]);

        $playerEvent = new PlayerEvent($player);
        $playerEvent->setReason(EndCauseEnum::CLUMSINESS);

        $this->eventDispatcherService->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

        $I->assertEquals(GameStatusEnum::FINISHED, $player->getGameStatus());
        $I->assertEquals($greatBeyond, $player->getPlace());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchInfection(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'user' => $user]);

        $mushStatus = new ChargeStatus($player);
        $mushStatus
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setCharge(0)
        ;

        $playerEvent = new PlayerEvent($player);
        $playerEvent->setReason(ActionEnum::INFECT);

        $this->eventDispatcherService->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());

        $this->eventDispatcherService->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());

        $this->eventDispatcherService->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(2, $player->getStatuses());
        $I->assertEquals($room, $player->getPlace());
    }
}
