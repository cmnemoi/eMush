<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DisorderCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldHaveDiseasePointDecreasedWhenPlayerSleepsInAShrinkRoom(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 5 disease points
        $disorder->setDiseasePoint(5);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the disease point should decrease by 1
        $I->assertEquals(4, $disorder->getDiseasePoint());
    }

    public function shouldBeHealedWhenPlayerIsLaidDownWithShrinkInRoom(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 1 disease point
        $disorder->setDiseasePoint(1);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the disease should be removed
        $I->assertNull($this->chun->getMedicalConditionByName(DisorderEnum::DEPRESSION));
    }

    public function shouldPrintAPublicLogWhenTreatedByShrink(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a public room log reporting the disorder treatment progress
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => LogEnum::DISORDER_TREATED_PLAYER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldPrintTreatmentLogWithShrinkAndPatient(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a public room log reporting the disorder treatment progress
        $log = $I->grabEntityFromRepository(
            RoomLog::class,
            [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => LogEnum::DISORDER_TREATED_PLAYER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        // then the log should contain the shrink and the patient
        $I->assertEquals(
            expected: 'kuan_ti',
            actual: $log->getParameters()['character']
        );
        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['target_character']
        );
    }

    public function shouldPrintAPublicLogWhenCuredByShrink(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 1 disease point
        $disorder->setDiseasePoint(1);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a public room log reporting the disorder cure
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => LogEnum::DISORDER_CURED_PLAYER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldPrintCuredLogWithShrinkAndPatient(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 1 disease point
        $disorder->setDiseasePoint(1);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a public room log reporting the disorder cure
        $log = $I->grabEntityFromRepository(
            RoomLog::class,
            [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => LogEnum::DISORDER_CURED_PLAYER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        // then the log should contain the shrink and the patient
        $I->assertEquals(
            expected: 'kuan_ti',
            actual: $log->getParameters()['character']
        );
        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['target_character']
        );
    }

    public function shouldNotPrintTreatmentLogWhenCured(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 1 disease point
        $disorder->setDiseasePoint(1);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should not see a public room log reporting the disorder treatment progress
        $I->dontSeeInRepository(
            RoomLog::class,
            [
                'place' => $this->chun->getPlace()->getLogName(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => LogEnum::DISORDER_TREATED_PLAYER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldNotBeSelfHealedByAShrink(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 1 disease point
        $disorder->setDiseasePoint(1);

        // given Chun is a shrink
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then the disorder should not be removed
        $I->assertNotNull($this->chun->getMedicalConditionByName(DisorderEnum::DEPRESSION));
    }

    public function onlyOneShouldBeTreatedAtATime(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $depression = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 2 disease points
        $depression->setDiseasePoint(2);

        // given Chun has a paranoia (a disorder)
        $paranoia = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::PARANOIA,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 1 disease points
        $paranoia->setDiseasePoint(1);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when a new cycle is triggered
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then only one disorder should be treated
        $I->assertEquals(1, $depression->getDiseasePoint());
        $I->assertEquals(1, $paranoia->getDiseasePoint());
    }
}
