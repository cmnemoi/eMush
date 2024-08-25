<?php

namespace Mush\Tests\functional\Player\Service;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventService;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerServiceCest extends AbstractFunctionalTest
{
    private PlayerService $playerService;
    private DaedalusService $daedalusService;
    private EventService $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerService::class);
        $this->daedalusService = $I->grabService(DaedalusService::class);
        $this->eventService = $I->grabService(EventService::class);
    }

    public function testDeathHumanPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $this->daedalus->setDay(5);
        $this->daedalus->setCycle(3);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST);
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
        $I->assertCount(1, $this->daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(2, $this->daedalus->getPlayers()->getHumanPlayer());
        $I->assertCount(0, $this->daedalus->getPlayers()->getMushPlayer());
    }

    public function testDeathPlayerLooseTitles(FunctionalTester $I): void
    {
        $kuanTi = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::KUAN_TI);

        $this->daedalus = $this->daedalusService->attributeTitles($this->daedalus, new \DateTime());
        $I->assertNotEmpty($kuanTi->getTitles());
        $I->assertEquals($kuanTi->getTitles(), [TitleEnum::COMMANDER, TitleEnum::NERON_MANAGER, TitleEnum::COM_MANAGER]);

        // Given Kuan is now (horribly) dead.
        $this->playerService->playerDeath($kuanTi, EndCauseEnum::MANKAROG, new \DateTime());
        $this->eventService->callEvent(new DaedalusCycleEvent($this->daedalus, [], new \DateTime()), DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEmpty($kuanTi->getTitles());
    }

    public function testDeathMushPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $deadPlayer = $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(GameStatusEnum::FINISHED, $deadPlayer->getPlayerInfo()->getGameStatus());
        $I->assertEquals(PlayerStatusEnum::MUSH, $deadPlayer->getStatuses()->first()->getName());
        $I->assertCount(1, $this->daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $this->daedalus->getPlayers()->getMushPlayer());
        $I->assertCount(2, $this->daedalus->getPlayers()->getHumanPlayer());
    }

    public function testDeathEffectOnOtherPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player1;

        /** @var Player $player2 */
        $player2 = $this->player2;
        $player2->setMoralPoint(10);

        /** @var Player $mushPlayer */
        $mushPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $mushPlayer->setMoralPoint(10);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);
        $mushStatus = new ChargeStatus($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertEquals(9, $player2->getMoralPoint());
        $I->assertEquals(10, $mushPlayer->getMoralPoint());
        $I->assertCount(1, $this->daedalus->getPlayers()->getPlayerDead());
        $I->assertCount(1, $this->daedalus->getPlayers()->getMushPlayer());
        $I->assertCount(2, $this->daedalus->getPlayers()->getHumanPlayer());
    }

    public function testDeathEffectOnItems(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $room = $player->getPlace();

        /** @var ItemConfig $item */
        $item = $I->have(ItemConfig::class);
        $gameItem = new GameItem($player);
        $gameItem
            ->setName('item')
            ->setEquipment($item);

        $player->addEquipment($gameItem);

        $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $I->assertCount(2, $room->getPlayers());
        $I->assertCount(1, $room->getPlayers()->getPlayerAlive());
        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
    }

    public function testHandleNewCyclePointsEarned(FunctionalTester $I): void
    {
        $this->player1->setActionPoint(10);
        $this->player1->setMovementPoint(10);

        $I->refreshEntities($this->player1);

        $this->playerService->handleNewCycle($this->player1, new \DateTime());

        $I->assertEquals(11, $this->player1->getActionPoint());
        $I->assertEquals(11, $this->player1->getMovementPoint());
    }

    public function testEndPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $deathTime = new \DateTime();
        $this->playerService->playerDeath($player, EndCauseEnum::INJURY, $deathTime);

        $otherPlayer = $this->player2;

        $this->playerService->endPlayer($this->player, 'my end message', [$otherPlayer->getId()]);

        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $otherClosedPlayer = $otherPlayer->getPlayerInfo()->getClosedPlayer();

        $I->assertEquals($closedPlayer->getMessage(), 'my end message');
        $I->assertEquals($otherClosedPlayer->getMessage(), null);
        $I->assertEquals($closedPlayer->getLikes(), 0);
        $I->assertEquals($otherClosedPlayer->getLikes(), 1);
        $I->assertEquals($closedPlayer->getFinishedAt(), $deathTime);
    }

    public function testEndPlayerCannotLikeThemself(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $otherPlayer = $this->player2;

        $this->playerService->endPlayer($this->player, '', [$otherPlayer->getId(), $player->getId()]);

        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $otherClosedPlayer = $otherPlayer->getPlayerInfo()->getClosedPlayer();

        $I->assertEquals($closedPlayer->getLikes(), 0);
        $I->assertEquals($otherClosedPlayer->getLikes(), 1);
    }

    public function testEndPlayerCannotLikeOtherPlayerMultipleTimes(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $otherPlayer = $this->player2;

        $this->playerService->endPlayer($this->player, '', [$otherPlayer->getId(), $otherPlayer->getId(), $otherPlayer->getId()]);

        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $otherClosedPlayer = $otherPlayer->getPlayerInfo()->getClosedPlayer();

        $I->assertEquals($closedPlayer->getLikes(), 0);
        $I->assertEquals($otherClosedPlayer->getLikes(), 1);
    }

    public function testEndPlayerCannotLikePlayerFromDifferentDaedalus(FunctionalTester $I): void
    {
        $player = $this->player;
        $this->playerService->playerDeath($player, EndCauseEnum::INJURY, new \DateTime());

        $daedalus2 = $this->createDaedalus($I);
        $otherPlayer = $this->addPlayerByCharacter($I, $daedalus2, CharacterEnum::ANDIE);

        $this->playerService->endPlayer($this->player, '', [$otherPlayer->getId()]);

        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $otherClosedPlayer = $otherPlayer->getPlayerInfo()->getClosedPlayer();

        $I->assertEquals($closedPlayer->getLikes(), 0);
        $I->assertEquals($otherClosedPlayer->getLikes(), 0);
    }
}
