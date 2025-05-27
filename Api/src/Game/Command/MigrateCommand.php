<?php

namespace Mush\Game\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mush:migrate',
    description: 'Load configs data and update the schema if necessary.',
    hidden: false
)]
class MigrateCommand extends Command
{
    private CreateDaedalusCommand $createDaedalusCommand;
    private LoadConfigsDataCommand $loadConfigDataCommand;

    public function __construct(CreateDaedalusCommand $createDaedalusCommand, LoadConfigsDataCommand $loadConfigDataCommand)
    {
        parent::__construct();

        $this->createDaedalusCommand = $createDaedalusCommand;
        $this->loadConfigDataCommand = $loadConfigDataCommand;
    }

    protected function configure(): void
    {
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Execute the command in dev mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migrating database...');

        $this->updateSchema($output);
        $this->loadConfigData($output);
        $this->createDaedalusCommand->execute($input, $output);

        $io->success('Migration completed.');

        return Command::SUCCESS;
    }

    private function loadConfigData(OutputInterface $output): void
    {
        $loadConfigDataArguments = [
            'command' => 'mush:load-configs-data',
        ];

        $loadConfigDataInput = new ArrayInput($loadConfigDataArguments);
        $this->loadConfigDataCommand->execute($loadConfigDataInput, $output);
    }

    private function updateSchema(OutputInterface $output): void
    {
        $arguments = [
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ];

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);

        $application = $this->getApplication();
        if ($application === null) {
            throw new \RuntimeException('Application not found.');
        }
        $application->find('doctrine:migrations:migrate')->run($input, $output);
    }
}
