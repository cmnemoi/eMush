<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Game\Command;

use Mush\Game\Command\ToggleMaintenanceModeCommand;
use Mush\Tests\unit\Game\TestDoubles\FakeAdminService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
final class ToggleMaintenanceModeCommandTest extends TestCase
{
    private FakeAdminService $adminService;
    private ToggleMaintenanceModeCommand $command;
    private CommandTester $commandTester;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->adminService = new FakeAdminService();
        $this->command = new ToggleMaintenanceModeCommand($this->adminService);

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testShouldPutGameInMaintenanceWhenNotInMaintenance(): void
    {
        $this->givenGameIsNotInMaintenance();

        $exitCode = $this->whenIExecuteToggleMaintenanceCommand();

        $this->thenGameShouldBePutInMaintenance($exitCode);
    }

    public function testShouldRemoveGameFromMaintenanceWhenInMaintenance(): void
    {
        $this->givenGameIsInMaintenance();

        $exitCode = $this->whenIExecuteToggleMaintenanceCommand();

        $this->thenGameShouldBeRemovedFromMaintenance($exitCode);
    }

    private function givenGameIsNotInMaintenance(): void
    {
        $this->adminService->setIsInMaintenance(false);
    }

    private function givenGameIsInMaintenance(): void
    {
        $this->adminService->setIsInMaintenance(true);
    }

    private function whenIExecuteToggleMaintenanceCommand(): int
    {
        return $this->commandTester->execute([]);
    }

    private function thenGameShouldBePutInMaintenance(int $exitCode): void
    {
        self::assertTrue($this->adminService->isGameInMaintenance());
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Game has been put in maintenance.', $this->commandTester->getDisplay());
    }

    private function thenGameShouldBeRemovedFromMaintenance(int $exitCode): void
    {
        self::assertFalse($this->adminService->isGameInMaintenance());
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Game has been removed from maintenance.', $this->commandTester->getDisplay());
    }
}
