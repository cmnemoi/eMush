<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Daedalus\Event;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class TravelEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    
    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testHunterAlertIsDeletedIfNoHunterAttackingOnTravelLaunched(FunctionalTester $I): void
    {
        // given some (simple) hunters are spawn
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // when travel is launched
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // then no hunter is attacking, therefore no hunter alert is present
        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->dontSeeInRepository(Alert::class, [
            'name' => AlertEnum::HUNTER,
            'daedalus' => $this->daedalus,
        ]);
    }
}