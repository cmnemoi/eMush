<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Hunter\Service;

use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HunterServiceKillHunterCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterService $hunterService;
    private Hunter $hunter;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterService = $I->grabService(HunterService::class);

        // given 1 hunter is spawn
        $this->daedalus->setHunterPoints(10); // should be enough to unpool 1 hunter
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $this->hunter = $this->daedalus->getAttackingHunters()->first();
    }

    public function testKillHunterDeleteHunter(FunctionalTester $I): void
    {
        // given 1 hunter is attacking
        $I->assertCount(1, $this->daedalus->getAttackingHunters());

        // when we kill this hunter
        $this->hunterService->killHunter($this->hunter, [ActionEnum::SHOOT_HUNTER], $this->player1);

        // then there is no more hunter attacking
        $I->assertEmpty($this->daedalus->getAttackingHunters());
    }

    public function testKillHunterDeletesAlertIfItWasLastHunter(FunctionalTester $I): void
    {
        // given there is a hunter alert
        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);

        // when we kill this hunter
        $this->hunterService->killHunter($this->hunter, [ActionEnum::SHOOT_HUNTER], $this->player1);

        // then there is no hunter alert anymore
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::HUNTER]);
    }
}
