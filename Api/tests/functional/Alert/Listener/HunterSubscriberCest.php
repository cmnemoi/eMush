<?php

namespace Mush\Tests\functional\Alert\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Listener\HunterSubscriber;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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
        $space = $this->daedalus->getSpace();

        // no hunters or scrap at the beginning of the test
        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->assertEmpty($space->getEquipments());

        $this->daedalus->setHunterPoints(10); // should be enough to spawn one hunter
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);

        $hunter = $this->daedalus->getAttackingHunters()->first();
        $event = new HunterEvent(
            $hunter,
            VisibilityEnum::HIDDEN,
            ['test', ActionEnum::SHOOT_HUNTER],
            new \DateTime()
        );
        $event->setAuthor($this->player1);
        $this->eventService->callEvent($event, HunterEvent::HUNTER_DEATH);

        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]); // no hunter = no alert
        $I->assertNotEmpty($space->getEquipments()); // the hunter killed should drop some scrap
    }

    public function testOnUnpoolHunter(FunctionalTester $I)
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->hunterSubscriber->onUnpoolHunters($event);

        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);
    }
}
