<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlayerModifierEventCest
{
    private EventDispatcherInterface $eventDispatcherService;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDispatchMoralChange(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'user' => $user,
            'characterConfig' => $characterConfig,
            'moralPoint' => 5,
        ]);

        $playerEvent = new PlayerModifierEvent($player, -1, new \DateTime());

        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(4, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());

        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(3, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -2, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(1, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 6, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(7, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());
    }

    public function testDispatchSatietyChange(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'user' => $user,
            'characterConfig' => $characterConfig,
            'satiety' => 0,
        ]);

        $playerEvent = new PlayerModifierEvent($player, -1, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 2, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 1, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(3, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -1, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -27, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::HUNGER,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testDispatchMushSatietyChange(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'user' => $user,
            'characterConfig' => $characterConfig,
            'satiety' => 0,
        ]);

        $mushStatus = new Status($player);
        $mushStatus
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
        ;

        $playerEvent = new PlayerModifierEvent($player, -1, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 4, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -29, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }
}
