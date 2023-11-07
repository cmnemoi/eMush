<?php

namespace Mush\Game\Command;

use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\CharacterConfigRepository;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mush:validate-crew-death',
    description: 'Fill a new Daedalus',
    hidden: false
)]
class ValidateCrewDeathCommand extends Command
{
    private CharacterConfigRepository $characterConfigRepository;

    private PlayerServiceInterface $playerService;
    private PlayerInfoRepository $playerInfoRepository;
    private UserRepository $userRepository;

    public function __construct(CharacterConfigRepository $characterConfigRepository,
        PlayerServiceInterface $playerService,
        PlayerInfoRepository $playerInfoRepository,
        UserRepository $userRepository)
    {
        parent::__construct();
        $this->characterConfigRepository = $characterConfigRepository;
        $this->playerService = $playerService;
        $this->playerInfoRepository = $playerInfoRepository;
        $this->userRepository = $userRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Validating death for all crew member ...');

        /** @var CharacterConfig[] $allCharacter */
        $allCharacter = $this->characterConfigRepository->findAll();
        foreach ($allCharacter as $character) {
            $name = $character->getName();

            try {
                $user = $this->userRepository->loadUserByUsername($name);
                if ($user == null) {
                    $io->warning("$name does not have an account. Skipping ...");
                    continue;
                }
                $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
                if ($playerInfo == null) {
                    $io->warning("$name has already validated his death, nothing to do. Skipping ...");
                    continue;
                }
                $player = $playerInfo->getPlayer();
                if ($player == null) {
                    $io->warning("Player can't be found for $name. Skipping ...");
                    continue;
                }
                $this->playerService->endPlayer($player, 'Validated by command', []);
                $io->info("$name as validated his death. Ready to board !");
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $trace = $e->getTraceAsString();
                $io->warning("$name cannot validate his death : $message -> $trace");
            }
        }

        return Command::SUCCESS;
    }
}
