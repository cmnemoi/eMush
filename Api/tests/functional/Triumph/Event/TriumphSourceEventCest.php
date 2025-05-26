<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TriumphSourceEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldGiveTriumphOnDaedalusNewCycleEvent(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $event = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // human cyclic triumph
        $I->assertEquals(1, $this->player->getTriumph());
    }

    public function shouldGiveTriumphOnDaedalusEvent(FunctionalTester $I): void
    {
        $this->player->setTriumph(0);

        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);

        // return to sol human triumph
        $I->assertEquals(20, $this->player->getTriumph());
    }
}
