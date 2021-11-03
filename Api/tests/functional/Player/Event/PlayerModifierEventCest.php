<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
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

        $suicidalStatusConfig = new StatusConfig();
        $suicidalStatusConfig
            ->setName(PlayerStatusEnum::SUICIDAL)
            ->setGameConfig($gameConfig)
        ;
        $demoralizedStatusConfig = new StatusConfig();
        $demoralizedStatusConfig
            ->setName(PlayerStatusEnum::DEMORALIZED)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($suicidalStatusConfig);
        $I->haveInRepository($demoralizedStatusConfig);

        $playerEvent = new PlayerModifierEvent(
            $player,
            -1,
            EventEnum::PLAYER_DEATH,
            new \DateTime()
        );

        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(4, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());

        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(3, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent(
            $player,
            -2,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        $I->assertEquals(1, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 6, EventEnum::NEW_CYCLE, new \DateTime());
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

        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setName(PlayerStatusEnum::FULL_STOMACH)
            ->setGameConfig($gameConfig)
        ;
        $starvingStatusConfig = new StatusConfig();
        $starvingStatusConfig
            ->setName(PlayerStatusEnum::STARVING)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($fullStatusConfig);
        $I->haveInRepository($starvingStatusConfig);

        $playerEvent = new PlayerModifierEvent($player, -1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 2, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(3, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -27, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => StatusEventLogEnum::HUNGER,
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

        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setName(PlayerStatusEnum::FULL_STOMACH)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($fullStatusConfig);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $playerEvent = new PlayerModifierEvent($player, -1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, 4, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $playerEvent = new PlayerModifierEvent($player, -29, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }
}
