<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\ReturnToSol;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ReturnToSolCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ReturnToSol $returnToSolAction;

    private GameEquipment $commandTerminal;

    private StatisticRepositoryInterface $statisticRepository;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::RETURN_TO_SOL]);
        $this->returnToSolAction = $I->grabService(ReturnToSol::class);

        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        /** @var GameEquipmentServiceInterface $gameEquipmentService */
        $gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        /** @var StatusServiceInterface $statusService */
        $statusService = $I->grabService(StatusServiceInterface::class);

        // given I have a command terminal in Chun's room
        $this->commandTerminal = $gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMAND_TERMINAL,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun is focused on terminal
        $statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal,
        );
    }

    public function shouldNotBeExecutableIfPilgredIsNotFinished(FunctionalTester $I): void
    {
        // given Pilgred is not finished (default)

        $this->whenChunTriesToExecuteReturnToSolAction();

        $this->thenActionShouldNotBeExecutable(ActionImpossibleCauseEnum::NO_PILGRED, $I);
    }

    public function shouldKillPlayers(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenAllDaedalusPlayersShouldBeDead($I);
    }

    public function shouldKillPlayersWithSolReturnEndCause(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenAllDaedalusPlayersShouldBeDeadWithSolReturnEndCause($I);
    }

    public function shouldNotPrintPublicDeathLogs(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenNoPublicDeathLogsShouldBePrinted($I);
    }

    public function shouldFinishTheGame(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenTheGameShouldBeFinished($I);
    }

    public function shouldNotCreateDeathAnnouncements(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenNoDeathAnnouncementsShouldBeCreated($I);
    }

    public function shouldNotMakeLoseMoralePoints(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenNoPlayerShouldLoseMoralePoints($I);
    }

    public function shouldNotTriggerTraumaDiseases(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenNoTraumaDiseasesShouldBeTriggered($I);
    }

    public function shouldIncrementBackToRootStatisticForAlivePlayers(FunctionalTester $I): void
    {
        $this->givenPilgredIsFinished();
        $this->givenKuanTiIsDead();

        $this->whenChunExecutesReturnToSolAction();

        $this->thenBackToRootStatisticShouldBeIncrementedForAlivePlayers($I);
    }

    private function givenPilgredIsFinished(): void
    {
        $pilgred = $this->daedalus->getPilgred();
        $pilgred->makeProgressAndUpdateParticipationDate(100);
    }

    private function givenKuanTiIsDead(): void
    {
        $this->playerService->killPlayer($this->kuanTi, endReason: EndCauseEnum::DEPRESSION);
    }

    private function whenChunTriesToExecuteReturnToSolAction(): void
    {
        $this->returnToSolAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->chun,
            target: $this->commandTerminal
        );
    }

    private function whenChunExecutesReturnToSolAction(): void
    {
        $this->whenChunTriesToExecuteReturnToSolAction();
        $this->returnToSolAction->execute();
    }

    private function thenActionShouldNotBeExecutable(string $reason, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $reason,
            actual: $this->returnToSolAction->cannotExecuteReason(),
        );
    }

    private function thenAllDaedalusPlayersShouldBeDead(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertFalse($player->isAlive());
        }
    }

    private function thenAllDaedalusPlayersShouldBeDeadWithSolReturnEndCause(FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: EndCauseEnum::SOL_RETURN,
                actual: $player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
            );
        }
    }

    private function thenNoPublicDeathLogsShouldBePrinted(FunctionalTester $I): void
    {
        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::DEATH,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function thenTheGameShouldBeFinished(FunctionalTester $I): void
    {
        $I->assertTrue($this->daedalus->getDaedalusInfo()->isDaedalusFinished());
    }

    private function thenNoDeathAnnouncementsShouldBeCreated(FunctionalTester $I): void
    {
        $I->cantSeeInRepository(
            entity: Message::class,
            params: [
                'message' => LogEnum::DEATH,
            ]
        );
    }

    private function thenNoPlayerShouldLoseMoralePoints(FunctionalTester $I): void
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

    private function thenBackToRootStatisticShouldBeIncrementedForAlivePlayers(FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::BACK_TO_ROOT, $this->chun->getUser()->getId());
        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::BACK_TO_ROOT,
                'count' => 1,
                'userId' => $this->chun->getUser()->getId(),
                'isRare' => true,
            ],
            actual: $statistic->toArray()
        );

        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::BACK_TO_ROOT, $this->kuanTi->getUser()->getId());
        $I->assertNull($statistic);
    }
}
