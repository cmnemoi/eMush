<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Service\PlayerService;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerCycleEventCest extends AbstractFunctionalTest
{
    private ChooseSkillUseCase $chooseSkillUseCase;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private RoomLogRepository $roomLogRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
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
        $panicCrisis = $this->roomLogRepository->getOneBy([
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
        $chao = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHAO);
        $this->addSkillToPlayer(SkillEnum::SHOOTER, $I, $chao);
        $shooterSkill = $chao->getSkillByNameOrThrow(SkillEnum::SHOOTER);

        // given the player has 2 shoot points
        $shooterPointsStatus = $chao->getChargeStatusByNameOrThrow(SkillPointsEnum::SHOOTER_POINTS->toString());
        $this->statusService->updateCharge($shooterPointsStatus, -2, tags: [], time: new \DateTime());
        $I->assertEquals(expected: $shooterSkill->getSkillPoints(), actual: 2);

        // when a new day event is triggered
        $event = new PlayerCycleEvent(
            $chao,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then player should have 4 shoot points
        $I->assertEquals(expected: $shooterSkill->getSkillPoints(), actual: 4);

        // then I should see a private log informing for the gain
        /** @var RoomLog $roomlog * */
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $chao->getPlace()->getLogName(),
                'playerInfo' => $chao->getPlayerInfo(),
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

        // then player gains statistic
        $I->assertEquals(1, $this->player->getPlayerInfo()->getStatistics()->getLostCycles());
    }

    public function shrinkShouldGiveMoraleToLaidDownPlayers(FunctionalTester $I): void
    {
        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Janice is a shrink
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $janice);

        // given Chun has 10 morale points
        $this->chun->setMoralPoint(10);

        // given KT has 10 morale points
        $this->kuanTi->setMoralPoint(10);

        // when cycle change is triggered
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun should have 11 morale points
        $I->assertTrue($this->chun->getMoralPoint() === 11 || $this->chun->getMoralPoint() === 11 - $this->getPanicCrisisPlayerDamage());

        // then KT should have 10 morale points
        $I->assertTrue($this->kuanTi->getMoralPoint() === 10 || $this->kuanTi->getMoralPoint() === 10 - $this->getPanicCrisisPlayerDamage());
    }

    public function testGermaphobeMushDoesNotLoseMoraleAtCycleChange(FunctionalTester $I): void
    {
        // given I have a Mush player
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );

        // given this player is germaphobic
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GERMAPHOBE,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
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

        // then I don't see the germaphobe modifier room log
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'log' => LogEnum::GERMAPHOBE_MORALE_LOSS,
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function shrinkShouldNotGiveMoraleToHimself(FunctionalTester $I): void
    {
        // given Janice is lying down
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $janice,
            tags: [],
            time: new \DateTime()
        );

        // given Janice is a shrink
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $janice);

        // given Janice has 10 morale points
        $janice->setMoralPoint(10);

        // when cycle change is triggered
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Janice has the expected 10 morale points
        $expectedMoralPoint = 10;

        // Janice might have a panic crisis at cycle change which would reduce their morale points. handling this case to avoid false positives
        $panicCrisis = $this->roomLogRepository->getOneBy([
            'place' => $janice->getPlace()->getName(),
            'playerInfo' => $janice->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::PANIC_CRISIS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
        if ($panicCrisis) {
            $expectedMoralPoint -= $this->getPanicCrisisPlayerDamage();
        }

        // then Janice should have 10 morale points (or reduced due to the panic crisis)
        $I->assertEquals($expectedMoralPoint, $janice->getMoralPoint());
    }

    public function shrinkShouldGiveMoraleToOtherShrink(FunctionalTester $I): void
    {
        // given Janice is lying down
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $janice,
            tags: [],
            time: new \DateTime()
        );

        // given Janice is a shrink
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $janice);

        // given Janice has 10 morale points
        $janice->setMoralPoint(10);

        // given there is another shrink in the room
        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $paola);

        // when cycle change is triggered
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Janice has the expected 11 morale points
        $expectedMoralPoint = 11;

        // Janice might have a panic crisis at cycle change which would reduce their morale points. handling this case to avoid false positives
        $panicCrisis = $this->roomLogRepository->getOneBy([
            'place' => $janice->getPlace()->getName(),
            'playerInfo' => $janice->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::PANIC_CRISIS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
        if ($panicCrisis) {
            $expectedMoralPoint -= $this->getPanicCrisisPlayerDamage();
        }

        // then Janice should have 11 morale points (or reduced due to the panic crisis)
        $I->assertEquals($expectedMoralPoint, $janice->getMoralPoint());
    }

    public function shrinkShouldGiveMoraleToOtherLaidDownShrink(FunctionalTester $I): void
    {
        // given chun is laid down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given chun is a shrink
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $this->chun);

        // given chun has 10 morale points
        $this->chun->setMoralPoint(10);

        // given Kuan ti is a shrink
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $this->kuanTi);

        // given Kuan Ti has 10 morale points
        $this->kuanTi->setMoralPoint(10);

        // given KT is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );

        // when cycle change is triggered
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun and Kuan Ti should have 11 morale points
        $I->assertTrue($this->chun->getMoralPoint() === 11 || $this->chun->getMoralPoint() === 11 - $this->getPanicCrisisPlayerDamage());
        $I->assertTrue($this->kuanTi->getMoralPoint() === 11 || $this->kuanTi->getMoralPoint() === 11 - $this->getPanicCrisisPlayerDamage());
    }

    public function mankindOnlyHopeShouldReduceDailyMoralePointLossByOne(FunctionalTester $I): void
    {
        // given Chun is the mankind only hope
        $this->addSkillToPlayer(SkillEnum::MANKIND_ONLY_HOPE, $I, $this->chun);

        // given the daedalus is D1C8 so next cycle is a new day
        $this->daedalus->setDay(1);
        $this->daedalus->setCycle(8);

        // given player, Chun and Kuan Ti have 10 morale points
        $this->player->setMoralPoint(10);
        $this->chun->setMoralPoint(10);
        $this->kuanTi->setMoralPoint(10);

        // when the new cycle event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then player, Chun and Kuan Ti should have 9 morale points
        $I->assertTrue($this->player->getMoralPoint() === 9 || $this->player->getMoralPoint() === 9 - $this->getPanicCrisisPlayerDamage());
        $I->assertTrue($this->chun->getMoralPoint() === 9 || $this->chun->getMoralPoint() === 9 - $this->getPanicCrisisPlayerDamage());
        $I->assertTrue($this->kuanTi->getMoralPoint() === 9 || $this->kuanTi->getMoralPoint() === 9 - $this->getPanicCrisisPlayerDamage());
    }

    public function twoMankindOnlyHopeShouldStillReduceDailyMoralePointLossByOne(FunctionalTester $I): void
    {
        // given Chun and Kuan Ti are the mankind only hopes
        $this->addSkillToPlayer(SkillEnum::MANKIND_ONLY_HOPE, $I, $this->chun);
        $this->addSkillToPlayer(SkillEnum::MANKIND_ONLY_HOPE, $I, $this->kuanTi);

        // given the daedalus is D1C8 so next cycle is a new day
        $this->daedalus->setDay(1);
        $this->daedalus->setCycle(8);

        // given player, Chun and Kuan Ti have 10 morale points
        $this->player->setMoralPoint(10);
        $this->chun->setMoralPoint(10);
        $this->kuanTi->setMoralPoint(10);

        // when the new cycle event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then player, Chun and Kuan Ti should have 9 morale points
        $I->assertTrue($this->player->getMoralPoint() === 9 || $this->player->getMoralPoint() === 9 - $this->getPanicCrisisPlayerDamage());
        $I->assertTrue($this->chun->getMoralPoint() === 9 || $this->chun->getMoralPoint() === 9 - $this->getPanicCrisisPlayerDamage());
        $I->assertTrue($this->kuanTi->getMoralPoint() === 9 || $this->kuanTi->getMoralPoint() === 9 - $this->getPanicCrisisPlayerDamage());
    }

    public function mankindOnlyHopeDoesNotWorkIfHolderIsDead(FunctionalTester $I): void
    {
        // given Chun is the mankind only hope
        $this->addSkillToPlayer(SkillEnum::MANKIND_ONLY_HOPE, $I, $this->chun);

        // given Chun is dead
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::DEPRESSION,
            time: new \DateTime(),
        );

        // given KT has 10 morale points
        $this->kuanTi->setMoralPoint(10);

        // when the new day event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then player should have 8 morale points
        $I->assertTrue($this->kuanTi->getMoralPoint() === 8 || $this->kuanTi->getMoralPoint() === 8 - $this->getPanicCrisisPlayerDamage());
    }

    public function ensureTriumphIsGivenIfNotInactive(FunctionalTester $I): void
    {
        // Given the player has the status inactive.
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->player2,
            tags: [],
            time: new \DateTime()
        );

        $initialTriumph = $this->player2->getTriumph();

        // when the new day event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // The player should have the same amount of triumph as before.
        $I->assertSame($initialTriumph, $this->player2->getTriumph(), 'The triumph count has shifted when it shouldn\'t!');
    }

    public function hyperactivePreventsLyingDownGainAndGivesMovementPoint(FunctionalTester $I): void
    {
        $this->givenChunHasHyperactive();

        $this->givenChunIsLyingDown();

        $this->givenChunHasAPMP(0, 0);

        $this->whenNewCycleEventIsTriggered();

        $this->thenChunShouldHaveAPMP($I, 1, 2);
    }

    public function hyperactiveAndBrokenGravitySimulatorCancelOut(FunctionalTester $I): void
    {
        $this->givenChunHasHyperactive();

        $this->givenChunHasAPMP(0, 0);

        $this->givenGravitySimulatorIsBroken();

        $this->whenNewCycleEventIsTriggered();

        $this->thenChunShouldHaveAPMP($I, 1, 1);
    }

    private function givenChunHasHyperactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HYPERACTIVE,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenChunIsLyingDown(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenChunHasAPMP(int $AP, int $MP): void
    {
        $this->chun->setActionPoint($AP);
        $this->chun->setMovementPoint($MP);
    }

    private function givenGravitySimulatorIsBroken()
    {
        $gravitySimulator = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::GRAVITY_SIMULATOR,
            equipmentHolder: $this->daedalus->getPlaces()->first(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );
        $this->statusService->createStatusFromName(EquipmentStatusEnum::BROKEN, $gravitySimulator, [], new \DateTime());
    }

    private function whenNewCycleEventIsTriggered(): void
    {
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenChunShouldHaveAPMP(FunctionalTester $I, int $AP, int $MP): void
    {
        $I->assertEquals($AP, $this->chun->getActionPoint());
        $I->assertEquals($MP, $this->chun->getMovementPoint());
    }

    private function getPanicCrisisPlayerDamage(): int
    {
        return array_keys($this->daedalus->getGameConfig()->getDifficultyConfig()->getPanicCrisisPlayerDamage()->toArray())[0];
    }
}
