<?php

declare(strict_types=1);

namespace Mush\Game\Command;

use Mush\MetaGame\Service\AdminServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mush:toggle-maintenance',
    description: 'Toggle game maintenance mode (puts game in maintenance if not, removes from maintenance if in maintenance).',
    hidden: false
)]
final class ToggleMaintenanceModeCommand extends Command
{
    private AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        parent::__construct();
        $this->adminService = $adminService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->adminService->isGameInMaintenance()) {
            $this->adminService->removeGameFromMaintenance();
            $io->success('Game has been removed from maintenance.');
        } else {
            $this->adminService->putGameInMaintenance();
            $io->success('Game has been put in maintenance.');
        }

        return Command::SUCCESS;
    }
}
