<?php

declare(strict_types=1);

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
    private DeleteAllModifiersCommand $deleteAllModifiersCommand;
    private CreateAllModifiersCommand $createAllModifiersCommand;

    public function __construct(CreateDaedalusCommand $createDaedalusCommand, LoadConfigsDataCommand $loadConfigDataCommand, DeleteAllModifiersCommand $deleteAllModifiersCommand, CreateAllModifiersCommand $createAllModifiersCommand)
    {
        parent::__construct();

        $this->createDaedalusCommand = $createDaedalusCommand;
        $this->loadConfigDataCommand = $loadConfigDataCommand;
        $this->deleteAllModifiersCommand = $deleteAllModifiersCommand;
        $this->createAllModifiersCommand = $createAllModifiersCommand;
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
        $this->deleteAllModifiers($output);
        $this->loadConfigData($output);
        $this->createAllModifiers($output);
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

        try {
            $this->loadConfigDataCommand->execute($loadConfigDataInput, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>Error loading config data: ' . $e->getMessage() . '</error>');

            throw new \RuntimeException('Error loading config data: ' . $e->getMessage());
        }
    }

    private function deleteAllModifiers(OutputInterface $output): void
    {
        $loadConfigDataArguments = [
            'command' => 'mush:delete-all-modifiers',
        ];

        $deleteAllModifiersDataInput = new ArrayInput($loadConfigDataArguments);

        try {
            $this->deleteAllModifiersCommand->execute($deleteAllModifiersDataInput, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>Error deleting all modifiers: ' . $e->getMessage() . '</error>');

            throw new \RuntimeException('Error deleting all modifiers: ' . $e->getMessage());
        }
    }

    private function createAllModifiers(OutputInterface $output): void
    {
        $loadConfigDataArguments = [
            'command' => 'mush:create-all-modifiers',
        ];

        $createAllModifiersDataInput = new ArrayInput($loadConfigDataArguments);

        try {
            $this->createAllModifiersCommand->execute($createAllModifiersDataInput, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>Error creating all modifiers: ' . $e->getMessage() . '</error>');

            throw new \RuntimeException('Error creating all modifiers: ' . $e->getMessage());
        }
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

        try {
            $application->find('doctrine:migrations:migrate')->run($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>Error updating schema: ' . $e->getMessage() . '</error>');

            throw new \RuntimeException('Error updating schema: ' . $e->getMessage());
        }
    }
}
