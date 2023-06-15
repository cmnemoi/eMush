<?php

declare(strict_types=1);

namespace Mush\Game\Command;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mush:close-old-daedaluses',
    description: 'Close old Daedaluses (finished for more than 1 week)',
    hidden: false
)]
class CloseOldDaedalusesCommand extends Command
{
    private DaedalusServiceInterface $daedalusService;
    private PlayerServiceInterface $playerService;

    public function __construct(DaedalusServiceInterface $service, PlayerServiceInterface $playerService)
    {
        parent::__construct();

        $this->daedalusService = $service;
        $this->playerService = $playerService;
    }

    protected function configure(): void
    {
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Create a dev Daedalus. (French only)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Closing old Daedaluses...');

        $daedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();
        foreach ($daedaluses as $daedalus) {
            $this->closeOldDaedalus($daedalus);
        }

        return Command::SUCCESS;
    }

    private function closeOldDaedalus(Daedalus $daedalus): void
    {
        $finishDate = $daedalus->getFinishedAt();
        $now = new \DateTime();

        if ($finishDate && $finishDate->diff($now)->days < 7) {
            return;
        }

        /** @var Player $player */
        foreach ($daedalus->getPlayers() as $player) {
            $this->playerService->endPlayer($player, '');
        }

        $this->daedalusService->closeDaedalus(
            $daedalus,
            reasons: ['daedalus_closed_after_1_week'],
            date: $now
        );
    }
}
