<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\TravelToEden;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TravelToEdenCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private TravelToEden $travelToEdenAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $commandTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TRAVEL_TO_EDEN]);
        $this->travelToEdenAction = $I->grabService(TravelToEden::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->createCommandTerminal();
        $this->focusChunOnTerminal();
    }

    public function shouldNotBeExecutableIfPilgredIsNotFinished(FunctionalTester $I): void
    {
        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTriesToTravelToEden();

        $this->thenActionShouldNotBeExecutable(
            message: ActionImpossibleCauseEnum::NO_PILGRED,
            I: $I
        );
    }

    public function shouldNotBeExecutableIfEdenCoordinatesAreNotComputed(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunTriesToTravelToEden();

        $this->thenActionShouldNotBeExecutable(
            message: ActionImpossibleCauseEnum::EDEN_NOT_COMPUTED,
            I: $I
        );
    }

    public function shouldKillPlayers(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenAllPlayersShouldBeDead($I);
    }

    public function shouldKillPlayersWithEdenEndCause(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenAllPlayersShouldHaveEdenEndCause($I);
    }

    public function shouldNotPrintPublicDeathLogs(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenNoPublicDeathLogsShouldExist($I);
    }

    public function shouldFinishTheGame(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenGameShouldBeFinished($I);
    }

    public function shouldNotCreateDeathAnnouncements(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenNoDeathAnnouncementsShouldExist($I);
    }

    public function shouldNotMakeLoseMoralePoints(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenPlayersShouldNotLoseMoralePoints($I);
    }

    public function shouldNotTriggerTraumaDiseases(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->givenEdenCoordinatesAreComputed();

        $this->whenChunTravelsToEden();

        $this->thenNoTraumaDiseasesShouldBeTriggered($I);
    }

    private function createCommandTerminal(): void
    {
        $this->commandTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMAND_TERMINAL,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function focusChunOnTerminal(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal,
        );
    }

    private function givenPilgredIsFinished(): void
    {
        $pilgred = $this->daedalus->getPilgred();
        $pilgred->makeProgressAndUpdateParticipationDate(100);
    }

    private function givenEdenCoordinatesAreComputed(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EDEN_COMPUTED,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenChunTriesToTravelToEden(): void
    {
        $this->travelToEdenAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->chun,
            target: $this->commandTerminal
        );
    }

    private function whenChunTravelsToEden(): void
    {
        $this->whenChunTriesToTravelToEden();
        $this->travelToEdenAction->execute();
    }

    private function thenActionShouldNotBeExecutable(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->travelToEdenAction->cannotExecuteReason());
    }

    private function thenAllPlayersShouldBeDead(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertFalse($player->isAlive());
        }
    }

    private function thenAllPlayersShouldHaveEdenEndCause(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: EndCauseEnum::EDEN,
                actual: $player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
            );
        }
    }

    private function thenNoPublicDeathLogsShouldExist(FunctionalTester $I): void
    {
        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::DEATH,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function thenGameShouldBeFinished(FunctionalTester $I): void
    {
        $I->assertTrue($this->daedalus->getDaedalusInfo()->isDaedalusFinished());
    }

    private function thenNoDeathAnnouncementsShouldExist(FunctionalTester $I): void
    {
        $I->cantSeeInRepository(
            entity: Message::class,
            params: [
                'message' => LogEnum::DEATH,
            ]
        );
    }

    private function thenPlayersShouldNotLoseMoralePoints(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: $player->getPlayerInfo()->getCharacterConfig()->getInitMoralPoint(),
                actual: $player->getMoralPoint(),
            );
        }
    }

    private function thenNoTraumaDiseasesShouldBeTriggered(FunctionalTester $I): void
    {
        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::TRAUMA_DISEASE,
            ]
        );
    }
}
