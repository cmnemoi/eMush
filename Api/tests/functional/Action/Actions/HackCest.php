<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Hack;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class HackCest extends AbstractFunctionalTest
{
    private Action $hackActionConfig;
    private Hack $hackAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $this->player->changePlace($bridge);

        $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::FOCUSED]);

        $this->hackActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::HACK]);
        $this->hackAction = $I->grabService(Hack::class);
    }

    public function testHackSuccessGrantsFocusedStatus(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given player has a 100% chance to hack the command terminal
        $this->hackActionConfig->setSuccessRate(100);

        // when player hacks the command terminal
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);
        $this->hackAction->execute();

        // then player has the focused status
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function testHackSuccessFocusedStatusTargetsCommandTerminal(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given player has a 100% chance to hack the command terminal
        $this->hackActionConfig->setSuccessRate(100);

        // when player hacks the command terminal
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);
        $this->hackAction->execute();

        // then player's focused status targets the command terminal
        $focusedStatus = $this->player->getStatusByName(PlayerStatusEnum::FOCUSED);
        $I->assertEquals(
            expected: $commandTerminal,
            actual: $focusedStatus?->getTarget()
        );
    }

    public function testHackSuccessFocusedPrintsLog(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given player has a 100% chance to hack the command terminal
        $this->hackActionConfig->setSuccessRate(100);

        // when player hacks the command terminal
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);
        $this->hackAction->execute();

        // then a log is printed on the bridge
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::BRIDGE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::HACK_SUCCESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testHackFailDoesNotGrantFocusedStatus(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given player has a 0% chance to hack the command terminal
        $this->hackActionConfig->setSuccessRate(0);

        // when player hacks the command terminal
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);
        $this->hackAction->execute();

        // then player does not have the focused status
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function testHackFailFocusedPrintsLog(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given player has a 0% chance to hack the command terminal
        $this->hackActionConfig->setSuccessRate(0);

        // when player hacks the command terminal
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);
        $this->hackAction->execute();

        // then a log is printed on the bridge
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::BRIDGE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::HACK_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testHackNotVisibleIfPlayerDoesNotHaveHackerKitInInventory(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);

        // when loading the hack action
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);

        // then action is not visible
        $I->assertFalse($this->hackAction->isVisible());
    }

    public function testHackNotExecutableIfCommandTerminalIsBroken(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given command terminal is broken
        $brokenStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $brokenStatus = new Status($commandTerminal, $brokenStatusConfig);
        $I->haveInRepository($brokenStatus);

        // when loading the hack action
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);

        // then action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            actual: $this->hackAction->cannotExecuteReason()
        );
    }

    public function testHackNotVisibleByCommander(FunctionalTester $I): void
    {
        $commandTerminal = $this->givenACommandTerminalOnTheBridge($I);
        $this->givenAHackerKitInPlayerInventory($I);

        // given player is commander
        $this->player->setTitles([TitleEnum::COMMANDER]);

        // when loading the hack action
        $this->hackAction->loadParameters($this->hackActionConfig, $this->player, $commandTerminal);

        // then action is not visible
        $I->assertFalse($this->hackAction->isVisible());
    }

    private function givenACommandTerminalOnTheBridge(FunctionalTester $I): GameEquipment
    {
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $commandTerminal = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::BRIDGE));
        $commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig)
        ;
        $I->haveInRepository($commandTerminal);

        return $commandTerminal;
    }

    private function givenAHackerKitInPlayerInventory(FunctionalTester $I): GameEquipment
    {
        $hackerKitConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::HACKER_KIT]);
        $hackerKit = new GameItem($this->player);
        $hackerKit
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setEquipment($hackerKitConfig)
        ;
        $I->haveInRepository($hackerKit);

        return $hackerKit;
    }
}
