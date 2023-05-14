<?php

namespace Mush\Tests\Modifier\Event;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class CycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testLieDownStatus(FunctionalTester $I)
    {
        $event = new StatusEvent(PlayerStatusEnum::LYING_DOWN, $this->player1, [], new \DateTime());
        $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);

        $actionPointBefore = $this->player1->getActionPoint();

        $I->assertCount(1, $this->player1->getStatuses());
        $I->assertCount(1, $this->player1->getModifiers());

        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($actionPointBefore + 2, $this->player1->getActionPoint());
    }

    public function testAntisocialStatusCycleSubscriber(FunctionalTester $I)
    {
        $event = new StatusEvent(PlayerStatusEnum::ANTISOCIAL, $this->player1, [], new \DateTime());
        $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);

        $moralePointBefore1 = $this->player1->getMoralPoint();
        $moralePointBefore2 = $this->player2->getMoralPoint();

        $I->assertCount(1, $this->player1->getStatuses());
        $I->assertCount(1, $this->player1->getModifiers());

        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($moralePointBefore1 - 1, $this->player1->getMoralPoint());
        $I->assertEquals($moralePointBefore2, $this->player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'place' => $this->player1->getPlace()->getName(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::ANTISOCIAL_MORALE_LOSS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testFitfullSleepCycleSubscriber(FunctionalTester $I)
    {
        $event = new StatusEvent(PlayerStatusEnum::LYING_DOWN, $this->player1, [], new \DateTime());
        $this->eventService->callEvent($event, StatusEvent::STATUS_APPLIED);

        $actionPointBefore = $this->player1->getActionPoint();

        $I->assertCount(1, $this->player1->getStatuses());
        $I->assertCount(1, $this->player1->getModifiers());

        /** @var TriggerEventModifierConfig $fitfullModifierConfig */
        $fitfullModifierConfig = $I->grabEntityFromRepository(
            TriggerEventModifierConfig::class, ['name' => 'cycle1ActionLostRand16FitfullSleep']
        );

        $fitfullModifierConfig->setModifierActivationRequirements([]);
        $I->flushToDatabase($fitfullModifierConfig);

        $fitfullModifier = new GameModifier($this->player1, $fitfullModifierConfig);
        $I->haveInRepository($fitfullModifier);

        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals($actionPointBefore + 1, $this->player1->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::FITFULL_SLEEP,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
