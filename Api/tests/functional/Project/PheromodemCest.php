<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PheromodemCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PHEROMODEM),
            author: $this->chun,
            I: $I
        );
    }

    public function shouldMakeChannelAccessibleToHumanPlayers(FunctionalTester $I): void
    {
        $this->thenHumanPlayersAreInMushChannel($I);
    }

    private function thenHumanPlayersAreInMushChannel(FunctionalTester $I): void
    {
        foreach ($this->players->getHumanPlayer() as $player) {
            $I->assertTrue($this->mushChannel->isPlayerParticipant($player->getPlayerInfo()));
        }
    }
}
