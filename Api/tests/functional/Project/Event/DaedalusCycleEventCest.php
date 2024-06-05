<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class DaedalusCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldMakeProjectProgressWithNeronProjectThreadProject(FunctionalTester $I): void
    {
        // given Neron Thread Project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::NERON_PROJECT_THREAD),
            author: $this->chun,
            I: $I
        );

        // when cycle changes
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Plasma Shield project should have a 5% progress
        $plasmaShield = $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD);
        $I->assertEquals(5, $plasmaShield->getProgress());
    }
}