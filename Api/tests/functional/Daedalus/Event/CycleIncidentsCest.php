<?php

declare(strict_types=1);

namespace Mush\Daedalus\Tests\Functional\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CycleIncidentsCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldDispatchIncidents(FunctionalTester $I): void
    {
        // given daedalus is full
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given daedalus has 20 incident points
        $this->daedalus->addIncidentPoints(20);

        // when a new cycle passes
        $this->eventService->callEvent(
            event: new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime()),
            name: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE
        );

        // then daedalus should have less incident points
        $I->assertLessThan(20, $this->daedalus->getIncidentPoints());
    }
}
