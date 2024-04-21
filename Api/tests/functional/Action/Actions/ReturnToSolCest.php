<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
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
    private Action $actionConfig;
    private ReturnToSol $returnToSolAction;

    private GameEquipment $commandTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::RETURN_TO_SOL]);
        $this->returnToSolAction = $I->grabService(ReturnToSol::class);

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

        // when Chun tries to execute ReturnToSol action
        $this->returnToSolAction->loadParameters($this->actionConfig, $this->chun, $this->commandTerminal);

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NO_PILGRED,
            actual: $this->returnToSolAction->cannotExecuteReason(),
        );
    }

    public function shouldKillPlayers(FunctionalTester $I): void
    {
        // given Pilgred is finished
        $pilgred = $this->daedalus->getPilgred();
        $pilgred->makeProgress(100);

        // when Chun executes ReturnToSol action
        $this->returnToSolAction->loadParameters($this->actionConfig, $this->chun, $this->commandTerminal);
        $this->returnToSolAction->execute();

        // then all Daedalus players should be dead
        foreach ($this->players as $player) {
            $I->assertFalse($player->isAlive());
        }
    }

    public function shouldKillPlayersWithSolReturnEndCause(FunctionalTester $I): void
    {
        // given Pilgred is finished
        $pilgred = $this->daedalus->getPilgred();
        $pilgred->makeProgress(100);

        // when Chun executes ReturnToSol action
        $this->returnToSolAction->loadParameters($this->actionConfig, $this->chun, $this->commandTerminal);
        $this->returnToSolAction->execute();

        // then all Daedalus players should be dead with Sol Return end cause
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: EndCauseEnum::SOL_RETURN,
                actual: $player->getPlayerInfo()->getClosedPlayer()->getEndCause(),
            );
        }
    }

    public function shouldNotPrintPublicDeathLogs(FunctionalTester $I): void
    {
        // given Pilgred is finished
        $pilgred = $this->daedalus->getPilgred();
        $pilgred->makeProgress(100);

        // when Chun executes ReturnToSol action
        $this->returnToSolAction->loadParameters($this->actionConfig, $this->chun, $this->commandTerminal);
        $this->returnToSolAction->execute();

        // then no public death logs should be printed
        $I->cantSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::DEATH,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldFinishTheGame(FunctionalTester $I): void
    {
        // given Pilgred is finished
        $pilgred = $this->daedalus->getPilgred();
        $pilgred->makeProgress(100);

        // when Chun executes ReturnToSol action
        $this->returnToSolAction->loadParameters($this->actionConfig, $this->chun, $this->commandTerminal);
        $this->returnToSolAction->execute();

        // then the game should be finished
        $I->assertTrue($this->daedalus->getDaedalusInfo()->isDaedalusFinished());
    }
}
