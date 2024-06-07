<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldFinishOnlyOneNeronProjectWithNeronProjectThread(FunctionalTester $I): void
    {
        $projects = [];
        // given proposed Plasma shield project is at 99% progress
        $plasmaShieldProject = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $plasmaShieldProject->propose();
        $plasmaShieldProject->makeProgress(99);
        $projects[] = $plasmaShieldProject;

        // given proposed Trail Reducer project is at 99% progress
        $trailReducerProject = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $trailReducerProject->propose();
        $trailReducerProject->makeProgress(99);
        $projects[] = $trailReducerProject;

        // given NERON project thread is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::NERON_PROJECT_THREAD),
            author: $this->player,
            I: $I
        );

        // when cycle changes
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then only one project should be finished
        $I->assertEquals(1, $this->daedalus->getFinishedProjects()->count());
    }
}
