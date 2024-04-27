<?php

declare(strict_types=1);

namespace Mush\tests\functional\Modifier\Listener;

use Mush\Modifier\Listener\ProjectEventSubscriber;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectEventSubscriberCest extends AbstractFunctionalTest
{
    private ProjectEventSubscriber $projectEventSubscriber;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectEventSubscriber = $I->grabService(ProjectEventSubscriber::class);
    }

    public function shouldCreateGameModifierOnProjectFinished(FunctionalTester $I): void
    {
        // given I have a project finished event
        $trailReducer = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $projectEvent = new ProjectEvent(
            project: $trailReducer,
            author: $this->chun,
        );

        // when I call onProjectFinished method
        $this->projectEventSubscriber->onProjectFinished($projectEvent);

        // then I should see a modifier created
        $I->assertNotEmpty($this->daedalus->getModifiers());
    }
}
