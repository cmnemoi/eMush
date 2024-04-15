<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\ExitTerminal;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExitTerminalCest extends AbstractFunctionalTest
{
    private Action $exitTerminalConfig;
    private ExitTerminal $exitTerminal;
    private StatusServiceInterface $statusService;
    private GameEquipment $commandTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $this->exitTerminalConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::EXIT_TERMINAL]);
        $this->exitTerminal = $I->grabService(ExitTerminal::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given a command terminal on the bridge
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($bridge);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig);
        $I->haveInRepository($this->commandTerminal);

        // given player is on the bridge
        $this->player->changePlace($bridge);
    }

    public function testExitTerminalSuccessRemovesFocusedStatus(FunctionalTester $I): void
    {
        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->commandTerminal,
        );

        // when player exits the terminal
        $this->exitTerminal->loadParameters($this->exitTerminalConfig, $this->player, $this->commandTerminal);
        $this->exitTerminal->execute();

        // then player is not focused on command terminal anymore
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }
}
