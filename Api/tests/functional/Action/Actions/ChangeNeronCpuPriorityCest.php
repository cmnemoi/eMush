<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\ChangeNeronCpuPriority;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ChangeNeronCpuPriorityCest extends AbstractFunctionalTest
{
    private Action $changeNeronCpuPriorityConfig;
    private ChangeNeronCpuPriority $changeNeronCpuPriorityAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $biosTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->changeNeronCpuPriorityConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::CHANGE_NERON_CPU_PRIORITY]);
        $this->changeNeronCpuPriorityAction = $I->grabService(ChangeNeronCpuPriority::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given I have a Nexus on this Daedalus
        $nexus = $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);

        // given player is in the nexus
        $this->player->changePlace($nexus);

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

    public function testChangeNeronCpuPriorityShouldSetPriorityToExpectedValue(FunctionalTester $I): void
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
    }
}
