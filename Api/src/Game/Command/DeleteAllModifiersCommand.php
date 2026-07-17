<?php

declare(strict_types=1);

namespace Mush\Game\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Modifier\Service\ModifierDeleteByDaedalusService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mush:delete-all-modifiers',
    description: 'Delete all modifiers from active Daedalus.',
    hidden: false
)]
class DeleteAllModifiersCommand extends Command
{
    private ModifierDeleteByDaedalusService $modifierDeleteByDaedalusService;
    private DaedalusRepository $daedalusRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ModifierDeleteByDaedalusService $modifierDeleteByDaedalusService, DaedalusRepository $daedalusRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->modifierDeleteByDaedalusService = $modifierDeleteByDaedalusService;
        $this->daedalusRepository = $daedalusRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Delete all modifiers...');

        try {
            $this->entityManager->beginTransaction();
            foreach ($this->daedalusRepository->findNonFinishedDaedaluses() as $daedalus) {
                if ($daedalus instanceof Daedalus) {
                    $this->modifierDeleteByDaedalusService->execute($daedalus);
                }
            }

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        $io->success('All modifiers deleted.');

        return Command::SUCCESS;
    }
}
