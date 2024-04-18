<?php

namespace Mush\Daedalus\Command;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mush:destroy-all-daedaluses',
    description: 'Destroy all Daedaluses. All non finished Daedaluses will finish with Super Nova ending',
    hidden: false
)]
class DestroyAllDaedalusesCommand extends Command
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(DaedalusServiceInterface $daedalusService)
    {
        parent::__construct();

        $this->daedalusService = $daedalusService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nonFinishedDaedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();
        if (\count($nonFinishedDaedaluses) === 0) {
            $output->writeln('No Daedalus to destroy.');

            return Command::SUCCESS;
        }

        foreach ($nonFinishedDaedaluses as $daedalus) {
            $output->writeln('Destroying all Daedaluses in a Super Nova...');
            $this->daedalusService->endDaedalus($daedalus, EndCauseEnum::SUPER_NOVA, new \DateTime());
        }

        $output->writeln('All Daedalus destroyed successfully.');

        return Command::SUCCESS;
    }
}
