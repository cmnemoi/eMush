<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Enum\ProjectName;
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

        // given Daedalus has a PILGRED project
        $this->createProject(
            ProjectName::PILGRED,
            $I
        );
    }

    public function testShouldNotBeExecutableIfPilgredIsNotFinished(FunctionalTester $I): void
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
}
