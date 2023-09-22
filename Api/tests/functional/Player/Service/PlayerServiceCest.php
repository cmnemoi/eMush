<?php

namespace Mush\Tests\functional\Player\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PlayerServiceCest extends AbstractFunctionalTest
{
    private PlayerService $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerService::class);
    }

    public function testDeathHumanPlayer(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['day' => 5, 'cycle' => '3']);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->seeInRepository(ClosedPlayer::class, [
            'endCause' => EndCauseEnum::INJURY,
            'dayDeath' => 5,
            'cycleDeath' => 3,
        ]);

        $I->assertEquals(GameStatusEnum::FINISHED, $deadPlayer->getPlayerInfo()->getGameStatus());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $daedalus->getPlayers()->getHumanPlayer());
        $I->assertCount(0, $daedalus->getPlayers()->getMushPlayer());
    }

    public function testDeathMushPlayer(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['day' => 5, 'cycle' => '3']);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(GameStatusEnum::FINISHED, $deadPlayer->getPlayerInfo()->getGameStatus());
        $I->assertEquals(PlayerStatusEnum::MUSH, $deadPlayer->getStatuses()->first()->getName());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $daedalus->getPlayers()->getMushPlayer());
        $I->assertCount(0, $daedalus->getPlayers()->getHumanPlayer());
    }

    public function testDeathEffectOnOtherPlayer(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $player2->setMoralPoint(10);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $mushPlayer->setPlayerVariables($characterConfig);
        $mushPlayer->setMoralPoint(10);
        $mushPlayerInfo = new PlayerInfo($mushPlayer, $user, $characterConfig);

        $I->haveInRepository($mushPlayerInfo);
        $mushPlayer->setPlayerInfo($mushPlayerInfo);
        $I->refreshEntities($mushPlayer);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(9, $player2->getMoralPoint());
        $I->assertEquals(10, $mushPlayer->getMoralPoint());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $daedalus->getPlayers()->getMushPlayer());
        $I->assertCount(2, $daedalus->getPlayers()->getHumanPlayer());
    }

    public function testDeathEffectOnItems(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['day' => 5, 'cycle' => '3']);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        /** @var ItemConfig $item */
        $item = $I->have(ItemConfig::class);
        $gameItem = new GameItem($player);
        $gameItem
            ->setName('item')
            ->setEquipment($item)
        ;

        $player->addEquipment($gameItem);
        $player->setPlayerInfo($playerInfo);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertCount(1, $room->getPlayers());
        $I->assertCount(0, $room->getPlayers()->getPlayerAlive());
        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
    }

    public function testHandleNewCyclePointsEarned(FunctionalTester $I)
    {
        $this->player1->setActionPoint(10);
        $this->player1->setMovementPoint(10);

        $I->refreshEntities($this->player1);

        $this->playerService->handleNewCycle($this->player1, new \DateTime());

        $I->assertEquals(11, $this->player1->getActionPoint());
        $I->assertEquals(10, $this->player1->getMovementPoint());
    }
}
