<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Event;

use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
    }

    public function shouldPutDaedalusShieldToFiftyPoints(FunctionalTester $I): void
    {
        // when Plasma Shield project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->chun,
            I: $I
        );

        // then Daedalus has 50 points of Plasma Shield
        $I->assertEquals(50, $this->daedalus->getShield());
    }
}
