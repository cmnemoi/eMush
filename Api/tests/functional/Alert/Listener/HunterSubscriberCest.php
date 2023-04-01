<?php

namespace Mush\Tests\Alert\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Listener\HunterSubscriber;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;

class HunterSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterSubscriber $hunterSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterSubscriber = $I->grabService(HunterSubscriber::class);
    }

    public function testOnHunterDeath(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            1,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::POOL_HUNTERS);
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $hunter = $this->daedalus->getAttackingHunters()->first();
        $event = new HunterEvent(
            $hunter,
            VisibilityEnum::HIDDEN,
            ['test'],
            new \DateTime()
        );
        $event->setAuthor($this->player1);
        $this->eventService->callEvent($event, HunterEvent::HUNTER_DEATH);

        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);
    }

    public function testOnUnpoolHunter(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            1,
            ['test'],
            new \DateTime()
        );
        $this->hunterSubscriber->onUnpoolHunters($event);

        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);
    }
}
