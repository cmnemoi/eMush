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

    public function shouldMakeDaedalusLoseOneLessOxygenWithOxyMoreProject(FunctionalTester $I): void
    {
        $this->givenDaedalusHasThreeOxygen();
        $this->givenOxyMoreProjectIsFinished($I);

        $this->whenCycleChanges();

        $this->thenDaedalusHasOneOxygen($I);
    }

    private function givenDaedalusHasThreeOxygen(): void
    {
        $this->daedalus->setOxygen(3);
    }

    private function givenOxyMoreProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::OXY_MORE),
            author: $this->player,
            I: $I
        );
    }

    private function whenCycleChanges(): void
    {
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenDaedalusHasOneOxygen(FunctionalTester $I): void
    {
        $I->assertEquals(1, $this->daedalus->getOxygen());
    }
}
