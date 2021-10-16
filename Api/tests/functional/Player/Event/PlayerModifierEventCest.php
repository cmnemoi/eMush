<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEventInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Config\StatusConfig;
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

        $playerEvent = new PlayerModifierEventInterface(
            $player,
            -1,
            EventEnum::PLAYER_DEATH,
            new \DateTime()
        );

        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);
        $I->assertEquals(4, $player->getMoralPoint());
        $I->assertCount(0, $player->getStatuses());

        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);
        $I->assertEquals(3, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface(
            $player,
            -2,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);
        $I->assertEquals(1, $player->getMoralPoint());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, 6, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);
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

        $playerEvent = new PlayerModifierEventInterface($player, -1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, 2, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, 1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(3, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, -1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(2, $player->getSatiety());
        $I->assertCount(0, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, -27, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
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

        $fullStatusConfig = new StatusConfig();
        $fullStatusConfig
            ->setName(PlayerStatusEnum::FULL_STOMACH)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($fullStatusConfig);

        $mushStatus = new Status($player);
        $mushStatus
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
        ;

        $playerEvent = new PlayerModifierEventInterface($player, -1, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-1, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, 4, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $playerEvent = new PlayerModifierEventInterface($player, -29, EventEnum::NEW_CYCLE, new \DateTime());
        $this->eventDispatcherService->dispatch($playerEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        $I->assertEquals(-25, $player->getSatiety());
        $I->assertCount(1, $player->getStatuses());
    }
}
