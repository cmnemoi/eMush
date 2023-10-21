<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AccessTerminal;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class AccessTerminalActionCest extends AbstractFunctionalTest
{
    private AccessTerminal $accessTerminal;
    private Action $accessTerminalConfig;
    private GameEquipment $astroTerminal;
    private GameEquipment $commandTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $this->accessTerminalConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::ACCESS_TERMINAL]);
        $this->accessTerminal = $I->grabService(AccessTerminal::class);

        // Astro terminal
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($bridge);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig)
        ;
        $I->haveInRepository($this->astroTerminal);

        // given there is a command terminal on the bridge
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $this->commandTerminal = new GameEquipment($bridge);
        $this->commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig)
        ;
        $I->haveInRepository($this->commandTerminal);

        // given player is on the bridge
        $this->player->changePlace($bridge);
    }

    public function testAccessTerminalSuccessAddFocusedStatus(FunctionalTester $I): void
    {
        // given player is not focus on astro terminal
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));

        // when player access astro terminal
        $this->accessTerminal->loadParameters($this->accessTerminalConfig, $this->player, $this->astroTerminal);
        $this->accessTerminal->execute();

        // then player is focused on astro terminal
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function testAccessTerminalNotExecutableIfPlayerDoesNotHaveTheRequiredTitle(FunctionalTester $I): void
    {
        // given player is not commander
        $I->assertFalse($this->player->hasTitle(TitleEnum::COMMANDER));

        // when player access command terminal
        $this->accessTerminal->loadParameters($this->accessTerminalConfig, $this->player, $this->commandTerminal);
        $this->accessTerminal->execute();

        // then the action is not executable and player is not focused on command terminal
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::TERMINAL_ROLE_RESTRICTED,
            actual: $this->accessTerminal->cannotExecuteReason(),
        );
    }

    public function testAccessTerminalIsExecutableIfPlayerHasTheRequiredTitle(FunctionalTester $I): void
    {
        // given player is commander
        $this->player->addTitle(TitleEnum::COMMANDER);

        // when player access command terminal
        $this->accessTerminal->loadParameters($this->accessTerminalConfig, $this->player, $this->commandTerminal);
        $this->accessTerminal->execute();

        // then the action is executable and player is focused on command terminal
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
        $I->assertNull($this->accessTerminal->cannotExecuteReason());
    }
}
