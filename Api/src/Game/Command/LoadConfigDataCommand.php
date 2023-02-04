<?php

namespace Mush\Game\Command;

use Mush\Game\Service\ConfigDataLoaderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mush:load-config-data',
    description: 'Load config data.',
    hidden: false
)]
class LoadConfigDataCommand extends Command
{
    private ConfigDataLoaderService $configDataLoaderService;

    public function __construct(ConfigDataLoaderService $configDataLoaderService)
    {
        parent::__construct();

        $this->configDataLoaderService = $configDataLoaderService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Loading config data...');

        $this->configDataLoaderService->loadData();

        $output->writeln('Config data loaded.');

        return Command::SUCCESS;
    }
}
