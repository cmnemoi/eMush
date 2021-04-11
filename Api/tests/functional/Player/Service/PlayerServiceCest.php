<?php

namespace functional\Player\Service;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class PlayerServiceCest
{
    private PlayerService $playerService;

    public function _before(FunctionalTester $I)
    {
        $this->playerService = $I->grabService(PlayerService::class);
    }

    public function testDeathHumanPlayer(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        $I->have(Place::class, ['name' => RoomEnum::GREAT_BEYOND, 'daedalus' => $daedalus]);

        $deadPlayerInfo = new DeadPlayerInfo();
        $I->haveInRepository($deadPlayerInfo);

        /** @var Player $player */
        $player = $I->have(Player::class, ['place' => $room, 'daedalus' => $daedalus, 'deadPlayerInfo' => $deadPlayerInfo]);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(RoomEnum::GREAT_BEYOND, $deadPlayer->getPlace()->getName());
        $I->assertEquals(EndCauseEnum::INJURY, $deadPlayer->getDeadPlayerInfo()->getEndStatus());
        $I->assertEquals(GameStatusEnum::FINISHED, $deadPlayer->getGameStatus());
        $I->assertCount(0, $deadPlayer->getStatuses());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $daedalus->getPlayers()->getHumanPlayer());
        $I->assertCount(0, $daedalus->getPlayers()->getMushPlayer());
    }

    public function testDeathMushPlayer(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        $I->have(Place::class, ['name' => RoomEnum::GREAT_BEYOND, 'daedalus' => $daedalus]);

        $deadPlayerInfo = new DeadPlayerInfo();
        $I->haveInRepository($deadPlayerInfo);

        /** @var Player $player */
        $player = $I->have(Player::class, ['place' => $room, 'daedalus' => $daedalus, 'deadPlayerInfo' => $deadPlayerInfo]);

        $status = new ChargeStatus($player);
        $status->setName(PlayerStatusEnum::MUSH);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(RoomEnum::GREAT_BEYOND, $deadPlayer->getPlace()->getName());
        $I->assertEquals(GameStatusEnum::FINISHED, $deadPlayer->getGameStatus());
        $I->assertEquals(EndCauseEnum::INJURY, $deadPlayer->getDeadPlayerInfo()->getEndStatus());
        $I->assertCount(1, $deadPlayer->getStatuses());
        $I->assertEquals(PlayerStatusEnum::MUSH, $deadPlayer->getStatuses()->first()->getName());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $daedalus->getPlayers()->getMushPlayer());
        $I->assertCount(0, $daedalus->getPlayers()->getHumanPlayer());
    }

    public function testDeathEffectOnOtherPlayer(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        $I->have(Place::class, ['name' => RoomEnum::GREAT_BEYOND, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $deadPlayerInfo = new DeadPlayerInfo();
        $I->haveInRepository($deadPlayerInfo);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
            'deadPlayerInfo' => $deadPlayerInfo,
            'characterConfig' => $characterConfig
        ]);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['place' => $room, 'daedalus' => $daedalus, 'characterConfig' => $characterConfig]);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, ['place' => $room, 'daedalus' => $daedalus, 'characterConfig' => $characterConfig]);
        $status = new ChargeStatus($mushPlayer);
        $status->setName(PlayerStatusEnum::MUSH);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(RoomEnum::GREAT_BEYOND, $deadPlayer->getPlace()->getName());
        $I->assertEquals(EndCauseEnum::INJURY, $deadPlayer->getDeadPlayerInfo()->getEndStatus());
        $I->assertEquals(9, $player2->getMoralPoint());
        $I->assertEquals(10, $mushPlayer->getMoralPoint());
        $I->assertCount(0, $deadPlayer->getStatuses());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $daedalus->getPlayers()->getMushPlayer());
        $I->assertCount(2, $daedalus->getPlayers()->getHumanPlayer());
    }

    public function testDeathEffectOnItems(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var Place $greatBeyond */
        $greatBeyond = $I->have(Place::class, ['name' => RoomEnum::GREAT_BEYOND, 'daedalus' => $daedalus]);

        $deadPlayerInfo = new DeadPlayerInfo();
        $I->haveInRepository($deadPlayerInfo);

        /** @var Player $player */
        $player = $I->have(Player::class, ['place' => $room, 'daedalus' => $daedalus, 'deadPlayerInfo' => $deadPlayerInfo]);

        $item = $I->have(ItemConfig::class);
        $gameItem = new GameItem();
        $gameItem
            ->setEquipment($item)
            ->setPlayer($player)
        ;

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(RoomEnum::GREAT_BEYOND, $deadPlayer->getPlace()->getName());
        $I->assertEquals(EndCauseEnum::INJURY, $deadPlayer->getDeadPlayerInfo()->getEndStatus());
        $I->assertCount(0, $player->getItems());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $greatBeyond->getEquipments());
    }
}
