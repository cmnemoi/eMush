<?php

namespace Mush\Tests\functional\Player\Service;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventService;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Config\CharacterConfig;
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
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;

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

        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $this->thenPlayerShouldBeDeadWithEndCause($I, $player, EndCauseEnum::INJURY);
        $this->thenDaedalusShouldHaveDeadPlayersCount($I, 1);
        $this->thenDaedalusShouldHaveHumanPlayersCount($I, 2);
        $this->thenDaedalusShouldHaveMushPlayersCount($I, 0);
    }

    public function testDeathPlayerLooseTitles(FunctionalTester $I): void
    {
        $kuanTi = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::KUAN_TI);

        $this->whenTitlesAreAttributedToDaedalus();
        $I->assertNotEmpty($kuanTi->getTitles());
        $I->assertEquals($kuanTi->getTitles(), [TitleEnum::COMMANDER, TitleEnum::NERON_MANAGER, TitleEnum::COM_MANAGER]);

        $this->whenPlayerIsKilledWithEndCause($kuanTi, EndCauseEnum::MANKAROG);
        $this->whenNewDaedalusCycleOccurs();

        $this->thenPlayerShouldHaveNoTitles($I, $kuanTi);
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

        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $this->thenPlayerShouldBeDeadWithEndCause($I, $player, EndCauseEnum::INJURY);
        $this->thenClosedDaedalusShouldHaveDeadPlayersCount($I, 1);
        $this->thenClosedDaedalusShouldHaveMushPlayersCount($I, 1);
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

        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $this->thenPlayerShouldHaveMoralPoint($I, $player2, 9);
        $this->thenPlayerShouldHaveMoralPoint($I, $mushPlayer, 10);
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
        $I->haveInRepository($gameItem);

        $player->addEquipment($gameItem);

        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $this->thenPlaceShouldHaveAlivePlayersCount($I, 1);
        $this->thenPlayerShouldHaveNoEquipments($I, $player);
        $this->thenPlaceShouldHaveEquipmentsCount($I, 1);
    }

    public function testHandleNewCyclePointsEarned(FunctionalTester $I): void
    {
        $this->player1->setActionPoint(10);
        $this->player1->setMovementPoint(10);

        $this->whenNewCycleIsHandledForPlayer($this->player1);

        $this->thenPlayerShouldHaveActionPoint($I, $this->player1, 11);
        $this->thenPlayerShouldHaveMovementPoint($I, $this->player1, 11);
    }

    public function testEndPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $deathTime = new \DateTime();
        $this->whenPlayerIsKilledWithEndCauseAtTime($player, EndCauseEnum::INJURY, $deathTime);

        $otherPlayer = $this->player2;

        $this->whenPlayerEndsWithMessageAndLikes($player, 'my end message', [$otherPlayer->getId()]);

        $this->thenClosedPlayerShouldHaveMessage($I, $player, 'my end message');
        $this->thenClosedPlayerShouldHaveMessage($I, $otherPlayer, null);
        $this->thenClosedPlayerShouldHaveLikes($I, $player, 0);
        $this->thenClosedPlayerShouldHaveLikes($I, $otherPlayer, 1);
        $this->thenClosedPlayerShouldHaveFinishedAt($I, $player, $deathTime);
    }

    public function testEndPlayerCannotLikeThemself(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $otherPlayer = $this->player2;

        $this->whenPlayerEndsWithMessageAndLikes($player, '', [$otherPlayer->getId(), $player->getId()]);

        $this->thenClosedPlayerShouldHaveLikes($I, $player, 0);
        $this->thenClosedPlayerShouldHaveLikes($I, $otherPlayer, 1);
    }

    public function testEndPlayerCannotLikeOtherPlayerMultipleTimes(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;
        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $otherPlayer = $this->player2;

        $this->whenPlayerEndsWithMessageAndLikes($player, '', [$otherPlayer->getId(), $otherPlayer->getId(), $otherPlayer->getId()]);

        $this->thenClosedPlayerShouldHaveLikes($I, $player, 0);
        $this->thenClosedPlayerShouldHaveLikes($I, $otherPlayer, 1);
    }

    public function testEndPlayerCannotLikePlayerFromDifferentDaedalus(FunctionalTester $I): void
    {
        $player = $this->player;
        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $daedalus2 = $this->createDaedalus($I);
        $otherPlayer = $this->addPlayerByCharacter($I, $daedalus2, CharacterEnum::ANDIE);

        $this->whenPlayerEndsWithMessageAndLikes($player, '', [$otherPlayer->getId()]);

        $this->thenClosedPlayerShouldHaveLikes($I, $player, 0);
        $this->thenClosedPlayerShouldHaveLikes($I, $otherPlayer, 0);
    }

    public function shouldCreateBeginnerStatusForFirstTimePlayer(FunctionalTester $I): void
    {
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        $player = $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::ANDIE);

        $this->thenPlayerShouldHaveBeginnerStatus($I, $player);
    }

    public function quarantineShouldSpawnOrganicWaste(FunctionalTester $I): void
    {
        $this->whenPlayerIsKilledWithEndCause($this->player, EndCauseEnum::QUARANTINE);

        $this->thenPlaceShouldHaveEquipmentByName($I, GameRationEnum::ORGANIC_WASTE);
    }

    public function createPlayerShouldCreateStartingItems(FunctionalTester $I): void
    {
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        $andie = $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::ANDIE);

        /** @var EquipmentConfig $itemConfig */
        foreach ($characterConfig->getStartingItems() as $itemConfig) {
            $this->thenPlayerShouldHaveEquipmentByName($I, $andie, $itemConfig->getEquipmentName());
        }
    }

    public function createPlayerShouldStartGameForUser(FunctionalTester $I): void
    {
        $user = UserFactory::createUser();
        $I->haveInRepository($user);

        $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::ANDIE);

        $this->thenUserShouldBeInGame($I, $user);
    }

    public function testHandleNewCycleIncrementsCycleCountForAlivePlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;

        $initialCycles = $player->getPlayerInfo()->getHumanCyclesCount();

        $this->whenNewCycleIsHandledForPlayer($player);

        $this->thenPlayerHumanCycleCountShouldBeIncremented($I, $player, $initialCycles);
    }

    public function testHandleNewCycleDoesNotIncrementCycleCountForDeadPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->player;

        $this->whenPlayerIsKilledWithEndCause($player, EndCauseEnum::INJURY);

        $cyclesAfterDeath = $player->getPlayerInfo()->getHumanCyclesCount();

        $this->whenNewCycleIsHandledForPlayer($player);

        $this->thenPlayerHumanCycleCountShouldNotBeIncremented($I, $player, $cyclesAfterDeath);
    }

    public function testHandleNewCycleDoesNotIncrementCycleCountForMushPlayer(FunctionalTester $I): void
    {
        /** @var Player $player */
        $player = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);

        $this->convertPlayerToMush($I, $player);

        $initialCycles = $player->getPlayerInfo()->getHumanCyclesCount();

        $this->whenNewCycleIsHandledForPlayer($player);

        $this->thenPlayerHumanCycleCountShouldNotBeIncremented($I, $player, $initialCycles);
    }

    private function whenPlayerIsKilledWithEndCause(Player $player, string $endCause): void
    {
        $this->playerService->killPlayer($player, $endCause, new \DateTime());
    }

    private function whenPlayerIsKilledWithEndCauseAtTime(Player $player, string $endCause, \DateTime $time): void
    {
        $this->playerService->killPlayer($player, $endCause, $time);
    }

    private function whenNewCycleIsHandledForPlayer(Player $player): void
    {
        $this->playerService->handleNewCycle($player, new \DateTime());
    }

    private function whenPlayerEndsWithMessageAndLikes(Player $player, string $message, array $likedPlayerIds): void
    {
        $this->playerService->endPlayer($player, $message, $likedPlayerIds);
    }

    private function whenTitlesAreAttributedToDaedalus(): void
    {
        $this->daedalusService->attributeTitles($this->daedalus, new \DateTime());
    }

    private function whenNewDaedalusCycleOccurs(): void
    {
        $this->eventService->callEvent(new DaedalusCycleEvent($this->daedalus, [], new \DateTime()), DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenPlayerShouldBeDeadWithEndCause(FunctionalTester $I, Player $player, string $endCause): void
    {
        $I->seeInRepository(ClosedPlayer::class, [
            'endCause' => $endCause,
            'dayDeath' => $this->daedalus->getDay(),
            'cycleDeath' => $this->daedalus->getCycle(),
        ]);

        $I->assertEquals(GameStatusEnum::FINISHED, $player->getPlayerInfo()->getGameStatus());
    }

    private function thenDaedalusShouldHaveDeadPlayersCount(FunctionalTester $I, int $count): void
    {
        $I->assertCount($count, $this->daedalus->getPlayers()->getPlayerDead());
    }

    private function thenDaedalusShouldHaveHumanPlayersCount(FunctionalTester $I, int $count): void
    {
        $I->assertCount($count, $this->daedalus->getPlayers()->getHumanPlayer());
    }

    private function thenDaedalusShouldHaveMushPlayersCount(FunctionalTester $I, int $count): void
    {
        $I->assertCount($count, $this->daedalus->getPlayers()->getMushPlayer());
    }

    private function thenPlayerShouldHaveTitles(FunctionalTester $I, Player $player, array $expectedTitles): void
    {
        $I->assertEquals($expectedTitles, $player->getTitles());
    }

    private function thenPlayerShouldHaveNoTitles(FunctionalTester $I, Player $player): void
    {
        $I->assertEmpty($player->getTitles());
    }

    private function thenClosedDaedalusShouldHaveDeadPlayersCount(FunctionalTester $I, int $count): void
    {
        $closedPlayers = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getPlayers();
        $I->assertCount($count, $closedPlayers->filter(static fn (ClosedPlayer $player) => $player->isDead()));
    }

    private function thenClosedDaedalusShouldHaveMushPlayersCount(FunctionalTester $I, int $count): void
    {
        $closedPlayers = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getPlayers();
        $I->assertCount($count, $closedPlayers->filter(static fn (ClosedPlayer $player) => $player->isMush()));
    }

    private function thenPlayerShouldHaveMoralPoint(FunctionalTester $I, Player $player, int $moralPoint): void
    {
        $I->assertEquals($moralPoint, $player->getMoralPoint());
    }

    private function thenPlaceShouldHaveAlivePlayersCount(FunctionalTester $I, int $count): void
    {
        $I->assertCount($count, $this->player->getPlace()->getPlayers()->getPlayerAlive());
    }

    private function thenPlayerShouldHaveNoEquipments(FunctionalTester $I, Player $player): void
    {
        $I->assertCount(0, $player->getEquipments());
    }

    private function thenPlaceShouldHaveEquipmentsCount(FunctionalTester $I, int $count): void
    {
        $I->assertCount($count, $this->player->getPlace()->getEquipments());
    }

    private function thenPlayerShouldHaveActionPoint(FunctionalTester $I, Player $player, int $actionPoint): void
    {
        $I->assertEquals($actionPoint, $player->getActionPoint());
    }

    private function thenPlayerShouldHaveMovementPoint(FunctionalTester $I, Player $player, int $movementPoint): void
    {
        $I->assertEquals($movementPoint, $player->getMovementPoint());
    }

    private function thenClosedPlayerShouldHaveMessage(FunctionalTester $I, Player $player, ?string $message): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $I->assertEquals($message, $closedPlayer->getMessage());
    }

    private function thenClosedPlayerShouldHaveLikes(FunctionalTester $I, Player $player, int $likes): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $I->assertEquals($likes, $closedPlayer->getLikes());
    }

    private function thenClosedPlayerShouldHaveFinishedAt(FunctionalTester $I, Player $player, \DateTime $finishedAt): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $I->assertEquals($finishedAt, $closedPlayer->getFinishedAt());
    }

    private function thenPlayerShouldHaveBeginnerStatus(FunctionalTester $I, Player $player): void
    {
        $I->assertTrue($player->hasStatus(PlayerStatusEnum::BEGINNER));
    }

    private function thenPlaceShouldHaveEquipmentByName(FunctionalTester $I, string $equipmentName): void
    {
        $I->assertTrue($this->player->getPlace()->hasEquipmentByName($equipmentName));
    }

    private function thenPlayerShouldHaveEquipmentByName(FunctionalTester $I, Player $player, string $equipmentName): void
    {
        $I->assertTrue($player->hasEquipmentByName($equipmentName));
    }

    private function thenUserShouldBeInGame(FunctionalTester $I, User $user): void
    {
        $I->assertTrue($user->isInGame());
    }

    private function thenPlayerHumanCycleCountShouldBeIncremented(FunctionalTester $I, Player $player, int $initialCycles): void
    {
        $I->assertEquals($initialCycles + 1, $player->getPlayerInfo()->getHumanCyclesCount());
    }

    private function thenPlayerHumanCycleCountShouldNotBeIncremented(FunctionalTester $I, Player $player, int $cyclesAfterDeath): void
    {
        $I->assertEquals($cyclesAfterDeath, $player->getPlayerInfo()->getHumanCyclesCount());
    }
}
