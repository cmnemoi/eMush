<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Delog;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\ValueObject\GameDate;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class DelogCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Delog $delog;

    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;
    private RoomLogServiceInterface $roomLogService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DELOG]);
        $this->delog = $I->grabService(Delog::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->roomLogService = $I->grabService(RoomLogServiceInterface::class);

        $this->givenPlayerIsMush();
        $this->addSkillToPlayer(SkillEnum::DEFACER, $I);

        $this->givenDaedalusIsAtDate(new GameDate($this->daedalus, day: 1, cycle: 1), $I);
    }

    public function shouldHideAllLogsInTheRoom(FunctionalTester $I): void
    {
        $roomLogs = $this->givenPreviousLogsInTheRoom();

        $this->whenPlayerUseDelogAction();

        $this->thenPreviousLogsShouldBeHidden($roomLogs, $I);
    }

    public function shouldPreventAnyLogToBeVisible(FunctionalTester $I): void
    {
        $this->givenPlayerUseDelogAction();

        $roomLog = $this->whenALogIsCreatedInTheRoom();
        $roomLogNoPlayer = $this->whenALogWhitoutPlayerIsCreatedInTheRoom();

        $this->thenLogShouldBeHidden($roomLog, $I);
        $this->thenLogShouldBeHidden($roomLogNoPlayer, $I);
    }

    public function delogShouldLastOneCycle(FunctionalTester $I): void
    {
        $this->givenPlayerUseDelogAction();

        $this->givenACyclePasses();

        $roomLog = $this->whenALogIsCreatedInTheRoom();

        $this->thenLogShouldBePublic($roomLog, $I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->whenPlayerUseDelogAction();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Les archives sont toutes brouillées… Qui a fait mumuse avec les senseurs encore !?',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::DELOGGED,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldNotBeExecutableIfDoneToday(FunctionalTester $I): void
    {
        $this->givenPlayerUseDelogAction();

        $this->whenPlayerUseDelogAction();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::DAILY_LIMIT,
            I: $I,
        );
    }

    public function shouldPreventNeronAnnouncementsForEventsOccruingTheRoom(FunctionalTester $I): void
    {
        $this->givenPlayerUseDelogAction();

        $this->whenPlayerDies();

        $this->thenIShouldNotSeeAnyNeronAnnouncements($I);
    }

    public function delogLogShouldBeHiddenWhenCyclePasses(FunctionalTester $I): void
    {
        $this->givenPlayerUseDelogAction();

        $this->givenACyclePasses();

        $roomLog = $I->grabEntityFromRepository(RoomLog::class, ['log' => LogEnum::DELOGGED, 'day' => 1, 'cycle' => 1]);

        $this->thenLogShouldBeHidden($roomLog, $I);
    }

    public function shouldNotHideNextCycleLogs(FunctionalTester $I): void
    {
        $this->givenPlayerUseDelogAction();

        $this->whenACyclePasses();

        $this->thenIShouldSeePrivateGainActionPointLog($I);
    }

    public function shouldHideDeloggedLogAtCycleOne(FunctionalTester $I): void
    {
        $this->givenDaedalusIsAtDate(new GameDate($this->daedalus, day: 1, cycle: 8), $I);

        $this->givenPlayerUseDelogAction();

        $this->whenACyclePasses();

        $roomLog = $I->grabEntityFromRepository(RoomLog::class, ['log' => LogEnum::DELOGGED, 'day' => 1, 'cycle' => 8]);

        $this->thenLogShouldBeHidden($roomLog, $I);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    /** @return RoomLog[] */
    private function givenPreviousLogsInTheRoom(): array
    {
        $roomLogs = [];
        for ($i = 0; $i < 2; ++$i) {
            $roomLogs[] = $this->roomLogService->createLog(
                logKey: 'rubbish',
                place: $this->player->getPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                player: $this->player,
            );
        }

        return $roomLogs;
    }

    private function givenPlayerUseDelogAction(): void
    {
        $this->delog->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->delog->execute();
    }

    private function givenACyclePasses(): void
    {
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function givenDaedalusIsAtDate(GameDate $date, FunctionalTester $I): void
    {
        $this->daedalus->setGameDate($date);
        $I->haveInRepository($this->daedalus);
    }

    private function whenPlayerUseDelogAction(): void
    {
        $this->delog->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->delog->execute();
    }

    private function whenALogIsCreatedInTheRoom(): RoomLog
    {
        return $this->roomLogService->createLog(
            logKey: 'rubbish',
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $this->player,
        );
    }

    private function whenALogWhitoutPlayerIsCreatedInTheRoom(): RoomLog
    {
        return $this->roomLogService->createLog(
            logKey: 'rubbish',
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
        );
    }

    private function whenPlayerDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::DEPRESSION,
            time: new \DateTime(),
        );
    }

    private function whenACyclePasses(): void
    {
        $daedalusCycleEvent = new DaedalusCycleEvent(
            daedalus: $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenPreviousLogsShouldBeHidden(array $roomLogs, FunctionalTester $I): void
    {
        array_map(
            static fn (RoomLog $roomLog) => $I->assertEquals(VisibilityEnum::HIDDEN, $roomLog->getVisibility()),
            $roomLogs
        );
    }

    private function thenLogShouldBeHidden(RoomLog $roomLog, FunctionalTester $I): void
    {
        $I->assertEquals(VisibilityEnum::HIDDEN, $roomLog->getVisibility());
    }

    private function thenLogShouldBePublic(RoomLog $roomLog, FunctionalTester $I): void
    {
        $I->assertEquals(VisibilityEnum::PUBLIC, $roomLog->getVisibility());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->delog->cannotExecuteReason());
    }

    private function thenIShouldNotSeeAnyNeronAnnouncements(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::PLAYER_DEATH,
            ]
        );
    }

    private function thenIShouldSeePrivateGainActionPointLog(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => PlayerModifierLogEnum::GAIN_ACTION_POINT,
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }
}
