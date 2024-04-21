<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\ChangeNeronCpuPriority;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChangeNeronCpuPriorityCest extends AbstractFunctionalTest
{
    private Action $changeNeronCpuPriorityConfig;
    private ChangeNeronCpuPriority $changeNeronCpuPriorityAction;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->changeNeronCpuPriorityConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::CHANGE_NERON_CPU_PRIORITY]);
        $this->changeNeronCpuPriorityAction = $I->grabService(ChangeNeronCpuPriority::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given I have a Nexus on this Daedalus
        $nexus = $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);

        // given Chun and KT are in the nexus
        $this->chun->changePlace($nexus);
        $this->kuanTi->changePlace($nexus);

        // given I have a BIOS terminal in the nexus
        $this->biosTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BIOS_TERMINAL,
            equipmentHolder: $nexus,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function testChangeNeronCpuPriorityNotVisibleIfPlayerIsNotFocusedOnBiosTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the bios terminal

        // when I try to change neron cpu priority to astronavigation
        $this->changeNeronCpuPriorityAction->loadParameters($this->changeNeronCpuPriorityConfig, $this->player, $this->biosTerminal, ['cpuPriority' => NeronCpuPriorityEnum::ASTRONAVIGATION]);
        $this->changeNeronCpuPriorityAction->execute();

        // then the action should not be visible
        $I->assertFalse($this->changeNeronCpuPriorityAction->isVisible());
    }

    public function testChangeNeronCpuPrioritySuccess(FunctionalTester $I): void
    {
        // given player is focused on the bios terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // when I try to change neron cpu priority to astronavigation
        $this->changeNeronCpuPriorityAction->loadParameters($this->changeNeronCpuPriorityConfig, $this->player, $this->biosTerminal, ['cpuPriority' => NeronCpuPriorityEnum::ASTRONAVIGATION]);
        $this->changeNeronCpuPriorityAction->execute();

        // then NERON CPU priority should be set to astronavigation
        $I->assertEquals(
            expected: NeronCpuPriorityEnum::ASTRONAVIGATION,
            actual: $this->daedalus->getDaedalusInfo()->getNeron()->getCpuPriority()
        );

        // then I should see a private room log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => ActionLogEnum::CHANGE_NERON_CPU_PRIORITY_SUCCESS,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function testChangeNeronCpuPriorityExecutableOnlyOnceADay(FunctionalTester $I): void
    {
        // given player is focused on the bios terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // given I change neron cpu priority to astronavigation
        $this->changeNeronCpuPriorityAction->loadParameters($this->changeNeronCpuPriorityConfig, $this->player, $this->biosTerminal, ['cpuPriority' => NeronCpuPriorityEnum::ASTRONAVIGATION]);
        $this->changeNeronCpuPriorityAction->execute();

        // when I try to change neron cpu priority to astronavigation again
        $this->changeNeronCpuPriorityAction->loadParameters($this->changeNeronCpuPriorityConfig, $this->player, $this->biosTerminal, ['cpuPriority' => NeronCpuPriorityEnum::ASTRONAVIGATION]);

        // then the action should not be executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->changeNeronCpuPriorityAction->cannotExecuteReason()
        );

        // given a new day passes
        $playerEvent = new PlayerEvent(
            player: $this->player,
            tags: [EventEnum::NEW_CYCLE, EventEnum::NEW_DAY],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::PLAYER_NEW_CYCLE);

        // when I try to change neron cpu priority to astronavigation again
        $this->changeNeronCpuPriorityAction->loadParameters($this->changeNeronCpuPriorityConfig, $this->player, $this->biosTerminal, ['cpuPriority' => NeronCpuPriorityEnum::ASTRONAVIGATION]);

        // then the action should be executable
        $I->assertNull($this->changeNeronCpuPriorityAction->cannotExecuteReason());
    }

    public function shouldImproveEfficiencyForPilgredProjectWithPilgredCpuPriority(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // given Chun is focused on the bios terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // when CPU priority is set to PILGRED
        $this->changeNeronCpuPriorityAction->loadParameters(
            $this->changeNeronCpuPriorityConfig,
            $this->chun,
            $this->biosTerminal,
            ['cpuPriority' => NeronCpuPriorityEnum::PILGRED]
        );
        $this->changeNeronCpuPriorityAction->execute();

        // then Chun's min efficiency should be 2
        $I->assertEquals(2, $this->chun->getMinEfficiencyForProject($pilgredProject));

        // then Chun's max efficiency should be 3
        $I->assertEquals(3, $this->chun->getMaxEfficiencyForProject($pilgredProject));
    }

    public function shouldResetPilgredEfficiencyWhenRemovingPilgredCpuPriority(FunctionalTester $I): void
    {
        // given I have the PILGRED project
        $pilgredProject = $this->daedalus->getPilgred();

        // given Chun is focused on the bios terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // given KT is focused on the bios terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $this->biosTerminal,
        );

        // given CPU priority is set to PILGRED
        $this->changeNeronCpuPriorityAction->loadParameters(
            $this->changeNeronCpuPriorityConfig,
            $this->chun,
            $this->biosTerminal,
            ['cpuPriority' => NeronCpuPriorityEnum::PILGRED]
        );
        $this->changeNeronCpuPriorityAction->execute();

        // when CPU priority is set to None
        $this->changeNeronCpuPriorityAction->loadParameters(
            $this->changeNeronCpuPriorityConfig,
            $this->kuanTi,
            $this->biosTerminal,
            ['cpuPriority' => NeronCpuPriorityEnum::NONE]
        );
        $this->changeNeronCpuPriorityAction->execute();

        // then Chun's min efficiency should be 1
        $I->assertEquals(1, $this->chun->getMinEfficiencyForProject($pilgredProject));

        // then Chun's max efficiency should be 1
        $I->assertEquals(1, $this->chun->getMaxEfficiencyForProject($pilgredProject));
    }
}
