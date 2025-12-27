<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerEventCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function shouldIncrementPendingLikesStatistic(FunctionalTester $I): void
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);

        $this->playerService->endPlayer($this->chun, 'Hello, World!', likedPlayers: [$this->kuanTi->getId(), $derek->getId()]);

        $I->assertEquals(
            expected: 1,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::LIKES,
                $this->kuanTi->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getCount(),
            message: 'Likes statistic should be incremented for Kuan Ti'
        );

        $I->assertEquals(
            expected: 1,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::LIKES,
                $derek->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getCount(),
            message: 'Likes statistic should be incremented for Derek'
        );
    }

    public function testHumanKillMushStatGain(FunctionalTester $I): void
    {
        $anotherHumanPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);

        $anotherMushPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->givenMushPlayer($anotherMushPlayer, $I);

        $this->givenMushPlayer($this->player2, $I);

        $this->whenPlayerXKilledByPlayerYForCause($this->player2, $this->player1, EndCauseEnum::INJURY);

        $this->thenPlayerShouldHaveMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherMushPlayer, $I);

        $this->thenPlayerShouldHaveTeamMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldHaveTeamMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherMushPlayer, $I);
    }

    public function testMushKillMushStatGain(FunctionalTester $I): void
    {
        $anotherHumanPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);

        $anotherMushPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->givenMushPlayer($anotherMushPlayer, $I);

        $this->givenMushPlayer($this->player1, $I);
        $this->givenMushPlayer($this->player2, $I);

        $this->whenPlayerXKilledByPlayerYForCause($this->player2, $this->player1, EndCauseEnum::INJURY);

        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherMushPlayer, $I);

        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldHaveTeamMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherMushPlayer, $I);
    }

    public function shouldNotGainStatOnHumanKill(FunctionalTester $I): void
    {
        $anotherHumanPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);

        $anotherMushPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->givenMushPlayer($anotherMushPlayer, $I);

        $this->whenPlayerXKilledByPlayerYForCause($this->player2, $this->player1, EndCauseEnum::INJURY);

        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherMushPlayer, $I);

        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherMushPlayer, $I);
    }

    public function shouldNotGainStatWhenDeathDidNotComeFromAssassination(FunctionalTester $I): void
    {
        $anotherHumanPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);

        $anotherMushPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->givenMushPlayer($anotherMushPlayer, $I);

        $this->givenMushPlayer($this->player2, $I);

        $this->whenPlayerXKilledByPlayerYForCause($this->player2, $this->player, EndCauseEnum::ABANDONED);

        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherMushPlayer, $I);

        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player2, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherMushPlayer, $I);
    }

    public function shouldNotGainStatWhenMushDiesWithNoAuthor(FunctionalTester $I): void
    {
        $anotherHumanPlayer = $this->player2;

        $anotherMushPlayer = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->givenMushPlayer($anotherMushPlayer, $I);

        $this->givenMushPlayer($this->player, $I);

        $this->whenPlayerXKilledByPlayerYForCause($this->player, null, EndCauseEnum::INJURY);

        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveMushKilledPendingStatistic($anotherMushPlayer, $I);

        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($this->player1, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherHumanPlayer, $I);
        $this->thenPlayerShouldNotHaveTeamMushKilledPendingStatistic($anotherMushPlayer, $I);
    }

    private function givenMushPlayer(Player $player, FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $player);
    }

    private function whenPlayerXKilledByPlayerYForCause(Player $victim, ?Player $killer, string $deathReason): void
    {
        $this->playerService->killPlayer(
            player: $victim,
            endReason: $deathReason,
            time: new \DateTime(),
            author: $killer
        );
    }

    private function thenPlayerShouldHaveMushKilledPendingStatistic(Player $player, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::MUSH_KILLED,
            userId: $player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $I->assertEquals(1, $pendingStatistic?->getCount());
    }

    private function thenPlayerShouldNotHaveMushKilledPendingStatistic(Player $player, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::MUSH_KILLED,
            userId: $player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $I->assertNull($pendingStatistic);
    }

    private function thenPlayerShouldHaveTeamMushKilledPendingStatistic(Player $player, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::TEAM_MUSH_KILLED,
            userId: $player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $I->assertEquals(1, $pendingStatistic?->getCount());
    }

    private function thenPlayerShouldNotHaveTeamMushKilledPendingStatistic(Player $player, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::TEAM_MUSH_KILLED,
            userId: $player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $I->assertNull($pendingStatistic);
    }
}
