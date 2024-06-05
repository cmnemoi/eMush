<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ChangeNeronCrewLock;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChangeNeronCrewLockCest extends AbstractFunctionalTest
{
    private ActionConfig $changeNeronCrewLockConfig;
    private ChangeNeronCrewLock $changeNeronCrewLockAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->changeNeronCrewLockConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CHANGE_NERON_CREW_LOCK]);
        $this->changeNeronCrewLockAction = $I->grabService(ChangeNeronCrewLock::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given I have a Bios terminal in Chun's room
        $this->biosTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BIOS_TERMINAL,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnBiosTerminal(FunctionalTester $I): void
    {
        // when Chun changes NERON crew lock to projects
        $this->changeNeronCrewLockAction->loadParameters(
            actionConfig: $this->changeNeronCrewLockConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
            parameters: ['crewLock' => NeronCrewLockEnum::PROJECTS->value],
        );

        // then action is not visible
        $I->assertFalse($this->changeNeronCrewLockAction->isVisible());
    }

    public function shouldNotBeVisibleIfPlayerIsNotInTerminalRoom(FunctionalTester $I): void
    {
        // given Chun is not in the same place as BIOS terminal
        $this->chun->changePlace($this->daedalus->getSpace());

        // given Chun is focused on BIOS terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // when Chun changes NERON crew lock to projects
        $this->changeNeronCrewLockAction->loadParameters(
            actionConfig: $this->changeNeronCrewLockConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
            parameters: ['crewLock' => NeronCrewLockEnum::PROJECTS->value],
        );

        // then action is not visible
        $I->assertFalse($this->changeNeronCrewLockAction->isVisible());
    }

    public function shouldChangeCrewLockToProjects(FunctionalTester $I): void
    {
        // given Chun is focused on BIOS terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // when Chun changes NERON crew lock to projects
        $this->changeNeronCrewLockAction->loadParameters(
            actionConfig: $this->changeNeronCrewLockConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
            parameters: ['crewLock' => NeronCrewLockEnum::PROJECTS->value],
        );
        $this->changeNeronCrewLockAction->execute();

        // then NERON crew lock should be projects
        $I->assertEquals(
            expected: NeronCrewLockEnum::PROJECTS,
            actual: $this->daedalus->getNeron()->getCrewLock()
        );
    }

    public function shouldCreateAPrivateLog(FunctionalTester $I): void
    {
        // given Chun is focused on BIOS terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // when Chun changes NERON crew lock to projects
        $this->changeNeronCrewLockAction->loadParameters(
            actionConfig: $this->changeNeronCrewLockConfig,
            actionProvider: $this->biosTerminal,
            player: $this->player,
            target: $this->biosTerminal,
            parameters: ['crewLock' => NeronCrewLockEnum::PROJECTS->value],
        );
        $this->changeNeronCrewLockAction->execute();

        // then a private log should be created in Chun's room
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getLogName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chun->getPlayerInfo(),
            'log' => ActionLogEnum::CHANGE_NERON_PARAMETER_SUCCESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
