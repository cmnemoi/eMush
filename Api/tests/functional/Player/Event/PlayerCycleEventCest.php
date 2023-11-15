<?php

namespace functional\Player\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class PlayerCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testDispatchCycleChange(FunctionalTester $I)
    {
        $startCycle = $this->daedalus->getCycle();
        $startDay = $this->daedalus->getDay();

        $playerAction = $this->player1->getActionPoint();
        $playerMovement = $this->player1->getMovementPoint();
        $playerSatiety = $this->player1->getSatiety();

        $I->assertCount(0, $this->daedalus->getModifiers());

        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(
            expected: $playerAction + 1,
            actual: $this->player1->getActionPoint()
        );
        $I->assertEquals(
            expected: $playerMovement + 1,
            actual: $this->player1->getMovementPoint()
        );
        $I->assertEquals(
            expected: $playerSatiety - 1,
            actual: $this->player1->getSatiety()
        );

        // dump($I->grabEntitiesFromRepository(RoomLog::class, ['log' => PlayerModifierLogEnum::GAIN_MOVEMENT_POINT]));

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player1->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::GAIN_ACTION_POINT,
            'visibility' => VisibilityEnum::PRIVATE,
            'day' => $startDay,
            'cycle' => $startCycle + 1,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player1->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::GAIN_MOVEMENT_POINT,
            'visibility' => VisibilityEnum::PRIVATE,
            'day' => $startDay,
            'cycle' => $startCycle + 1,
        ]);
    }

    public function testNoGravitySimulator(FunctionalTester $I)
    {
        $startCycle = $this->daedalus->getCycle();
        $startDay = $this->daedalus->getDay();

        $playerAction = $this->player1->getActionPoint();
        $playerMovement = $this->player1->getMovementPoint();
        $playerSatiety = $this->player1->getSatiety();

        $gravitySimulator = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::GRAVITY_SIMULATOR,
            equipmentHolder: $this->daedalus->getPlaces()->first(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );
        $this->statusService->createStatusFromName(EquipmentStatusEnum::BROKEN, $gravitySimulator, [], new \DateTime());

        $I->assertCount(2, $this->daedalus->getModifiers());

        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(
            expected: $playerAction + 1,
            actual: $this->player1->getActionPoint()
        );
        $I->assertEquals(
            expected: $playerMovement,
            actual: $this->player1->getMovementPoint()
        );
        $I->assertEquals(
            expected: $playerSatiety - 1,
            actual: $this->player1->getSatiety()
        );

        // dump($I->grabEntitiesFromRepository(RoomLog::class, ['log' => PlayerModifierLogEnum::GAIN_MOVEMENT_POINT]));

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player1->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::GAIN_ACTION_POINT,
            'visibility' => VisibilityEnum::PRIVATE,
            'day' => $startDay,
            'cycle' => $startCycle + 1,
        ]);
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->player1->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::GAIN_MOVEMENT_POINT,
            'visibility' => VisibilityEnum::PRIVATE,
            'day' => $startDay,
            'cycle' => $startCycle + 1,
        ]);
    }
}
