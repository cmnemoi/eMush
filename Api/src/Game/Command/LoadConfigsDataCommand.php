<?php

namespace Mush\Game\Command;

use Mush\Game\Service\ConfigDataLoaderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mush:load-configs-data',
    description: 'Load configs data.',
    hidden: false
)]
class LoadConfigsDataCommand extends Command
{
    private ConfigDataLoaderService $configDataLoaderService;

    public function __construct(ConfigDataLoaderService $configDataLoaderService)
    {
        parent::__construct();

        $this->configDataLoaderService = $configDataLoaderService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Loading configs data...');

        $this->configDataLoaderService->loadData();

        $output->writeln('Configs data loaded.');

        return Command::SUCCESS;
    }
}
