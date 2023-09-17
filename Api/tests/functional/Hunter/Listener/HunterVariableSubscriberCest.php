<?php

namespace Api\tests\functional\Hunter\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\Hunter\Listener\HunterVariableSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class HunterVariableSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterVariableSubscriber $hunterVariableSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterVariableSubscriber = $I->grabService(HunterVariableSubscriber::class);
    }

    public function testOnChangeVariableKillHunter(FunctionalTester $I)
    {
        $space = $this->daedalus->getSpace();

        // no hunters, scrap or hunter killed at the beginning of the test
        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->assertEmpty($space->getEquipments());
        $I->assertEquals(0, $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getNumberOfHuntersKilled());

        $this->daedalus->setHunterPoints(10); // should be enough to unpool 1 hunter
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $hunter = $this->daedalus->getAttackingHunters()->first();

        $hunterVariableEvent = new HunterVariableEvent(
            $hunter,
            HunterVariableEnum::HEALTH,
            -$hunter->getHealth(),
            ['test', ActionEnum::SHOOT_HUNTER],
            new \DateTime()
        );
        $hunterVariableEvent->setAuthor($this->player1);
        $this->hunterVariableSubscriber->onChangeVariable($hunterVariableEvent);

        $I->assertEmpty($this->daedalus->getAttackingHunters());
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getNumberOfHuntersKilled());
    }
}
