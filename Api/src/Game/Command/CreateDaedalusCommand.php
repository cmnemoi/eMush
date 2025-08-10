<?php

namespace Mush\Game\Command;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'mush:create-daedalus',
    description: 'Create a new Daedalus if none available.',
    hidden: false
)]
class CreateDaedalusCommand extends Command
{
    private DaedalusServiceInterface $service;
    private GameConfigServiceInterface $gameConfigService;

    public function __construct(DaedalusServiceInterface $service, GameConfigServiceInterface $gameConfigService)
    {
        parent::__construct();

        $this->service = $service;
        $this->gameConfigService = $gameConfigService;
    }

    protected function configure(): void
    {
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Create a dev Daedalus. (French only)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating Daedalus...');

        if ($input->getOption('dev')) {
            if ($this->service->existAvailableDaedalusWithName('dev')) {
                $io->info("A 'dev' Daedalus is already available.");

                return Command::SUCCESS;
            }
            $this->createDevDaedalus();
            $io->success("'dev' Daedalus created.");

            return Command::SUCCESS;
        }

        foreach (LanguageEnum::getAll() as $language) {
            if ($this->service->existAvailableDaedalusInLanguage($language)) {
                $io->info(\sprintf('A %s Daedalus is already available.', $language));
            } else {
                $this->createDaedalusForLanguage($language);
                $io->success(\sprintf('%s Daedalus created.', $language));
            }
        }

        return Command::SUCCESS;
    }

    private function createDevDaedalus(): void
    {
        $name = Uuid::v4()->toRfc4122();
        $language = LanguageEnum::FRENCH;
        $config = $this->gameConfigService->getConfigByName(GameConfigEnum::DEFAULT);

        $this->service->createDaedalus($config, $name, $language);
    }

    private function createDaedalusForLanguage(string $language): void
    {
        $name = Uuid::v4()->toRfc4122();
        $config = $this->gameConfigService->getConfigByName(GameConfigEnum::DEFAULT);

        $this->service->createDaedalus($config, $name, $language);
    }
}
