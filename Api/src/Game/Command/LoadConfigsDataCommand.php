<?php

namespace Mush\Game\Command;

use Mush\Game\Service\ConfigDataLoaderService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle($input, $output);

        $io->title('Loading configs data...');

        $this->configDataLoaderService->loadAllConfigsData();

        $io->success('Configs data loaded.');

        return Command::SUCCESS;
    }
}
