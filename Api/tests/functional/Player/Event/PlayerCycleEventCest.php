<?php

namespace Mush\Tests\functional\Player\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RoomLogRepository $roomLogRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->roomLogRepository = $I->grabService(RoomLogRepository::class);
    }

    public function testDispatchCycleChange(FunctionalTester $I): void
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

    public function testNoGravitySimulator(FunctionalTester $I): void
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

    public function testNewDayTriggersDailyMoraleLoss(FunctionalTester $I): void
    {
        // given the daedalus is D1C8 so next cycle is a new day
        $this->daedalus->setDay(1);
        $this->daedalus->setCycle(8);

        // when the new cycle event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then the player has the expected morale points
        $expectedMoralPoint = $this->player->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint() + PlayerService::DAY_MORAL_CHANGE;

        // player might have a panic crisis at cycle change which would reduce their morale points. handling this case to avoid false positives
        $panicCrisis = $this->roomLogRepository->findOneBy([
            'place' => $this->player->getPlace()->getName(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::PANIC_CRISIS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
        if ($panicCrisis) {
            $expectedMoralPoint -= $this->getPanicCrisisPlayerDamage();
        }

        $I->assertEquals(
            expected: $expectedMoralPoint,
            actual: $this->player->getMoralPoint()
        );

        // then I see a room log with the daily morale loss
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'log' => PlayerModifierLogEnum::LOSS_MORAL_POINT,
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );

        // then I see a unique log explaining the cause of the morale loss
        $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => PlayerModifierLogEnum::DAILY_MORALE_LOSS,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function testAsocialMushDoesNotLoseMoraleAtCycleChange(FunctionalTester $I): void
    {
        // given I have a Mush player
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );

        // given this player is asocial
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::ANTISOCIAL,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );

        // given there is another player in room
        $I->assertEquals(
            expected: 2,
            actual: $this->player->getPlace()->getPlayers()->getPlayerAlive()->count()
        );

        // when a new cycle event is triggered
        $event = new PlayerCycleEvent(
            $this->player,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the player should not lose morale
        $expectedMoralPoint = $this->player->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint();
        $I->assertEquals(
            expected: $expectedMoralPoint,
            actual: $this->player->getMoralPoint()
        );

        // then I don't see the antisocial modifier room log
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'log' => LogEnum::ANTISOCIAL_MORALE_LOSS,
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function testMushDoesNotLoseMoraleAtDayChange(FunctionalTester $I): void
    {
        // given I have a Mush player
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );

        // when a new day event is triggered
        $event = new PlayerCycleEvent(
            $this->player,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the player should not lose morale
        $expectedMoralPoint = $this->player->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint();
        $I->assertEquals(
            expected: $expectedMoralPoint,
            actual: $this->player->getMoralPoint()
        );

        // then I don't see the daily morale loss log
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'log' => PlayerModifierLogEnum::DAILY_MORALE_LOSS,
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function testShooterGetsShootPointsAtDayChange(FunctionalTester $I): void
    {
        // given I have a Shooter player
        /** @var ChargeStatus $shooterSkill * */
        $shooterSkill = $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHOOTER,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );

        // given the player has 1 shoot points
        $this->statusService->updateCharge($shooterSkill, -1, tags: [], time: new \DateTime());
        $I->assertEquals(expected: $shooterSkill->getCharge(), actual: 1);

        // when a new day event is triggered
        $event = new PlayerCycleEvent(
            $this->player,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then player should have 3 shoot points
        $I->assertEquals(expected: 3, actual: $shooterSkill->getCharge());

        // then I should see a private log informing for the gain
        /** @var RoomLog $roomlog * */
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => StatusEventLogEnum::GAIN_SHOOT_POINT,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );

        // then the log should print the right quantity
        $logParameters = $roomLog->getParameters();
        $I->assertEquals(
            expected: 2,
            actual: $logParameters['quantity'],
        );
    }

    public function testPlayerDiesTheCycleTheyGetAtZeroMoralePoints(FunctionalTester $I): void
    {
        // given player has 1 morale points
        $this->player->setMoralPoint(1);

        // given the daedalus is D1C8 so next cycle is a new day
        $this->daedalus->setDay(1);
        $this->daedalus->setCycle(8);

        // when the new cycle event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then the player should have 0 morale points
        $I->assertEquals(
            expected: 0,
            actual: $this->player->getMoralPoint()
        );

        // then the player should be dead
        $I->assertFalse($this->player->isAlive());
    }

    public function testLostStatusMakesPlayerLosesTwoMoralePointsAtCycleChange(FunctionalTester $I): void
    {
        // given player is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );

        // given player has 10 morale points
        $this->player->setMoralPoint(10);

        // when cycle change is triggered for status
        $cycleEvent = new PlayerCycleEvent($this->player, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then player should have 8 morale points
        $I->assertEquals(8, $this->player->getMoralPoint());

        // then player should have a private log telling them they are lost
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'log' => LogEnum::LOST_ON_PLANET,
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );

        // then the log is properly parametrized
        $I->assertEquals(
            expected: $this->player->getLogName(),
            actual: $roomLog->getParameters()['target_character']
        );
    }

    public function shouldGiveOneMoralePointToLyingDownPlayersWithShrinkInTheRoom(FunctionalTester $I): void
    {
        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given KT is a shrink
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has 10 morale points
        $this->chun->setMoralPoint(10);

        // when cycle change is triggered
        $cycleEvent = new PlayerCycleEvent($this->chun, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then Chun should have 11 morale points
        $I->assertEquals(11, $this->chun->getMoralPoint());
    }

    private function getPanicCrisisPlayerDamage(): int
    {
        return array_keys($this->daedalus->getGameConfig()->getDifficultyConfig()->getPanicCrisisPlayerDamage()->toArray())[0];
    }
}
