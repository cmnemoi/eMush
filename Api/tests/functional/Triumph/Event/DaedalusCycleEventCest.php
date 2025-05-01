<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldGiveCycleHumanTriumphToHumans(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $event = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(1, $this->player->getTriumph());
    }
}
