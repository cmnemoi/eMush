<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ZetaWavesCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::ZETA_WAVES),
            author: $this->chun,
            I: $I
        );
    }

    public function shouldGivePlayersTwoExtraPrivateChanels(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertEquals(expected: 5, actual: $player->getMaxPrivateChannels());
        }
    }
}
