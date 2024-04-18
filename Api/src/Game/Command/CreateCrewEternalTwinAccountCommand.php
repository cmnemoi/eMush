<?php

namespace Mush\Game\Command;

use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\CharacterConfigRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'mush:create-crew',
    description: 'Create an account for each character in the EternalTwin database',
    hidden: false
)]
class CreateCrewEternalTwinAccountCommand extends Command
{
    private HttpClientInterface $httpClient;
    private CharacterConfigRepository $characterConfigRepository;
    private string $identityServerUrl;

    public function __construct(HttpClientInterface $httpClient, CharacterConfigRepository $characterConfigRepository)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
        $this->characterConfigRepository = $characterConfigRepository;
        $this->identityServerUrl = $_ENV['IDENTITY_SERVER_URI'];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating crew account ...');

        /** @var CharacterConfig[] $allCharacter */
        $allCharacter = $this->characterConfigRepository->findAll();
        foreach ($allCharacter as $character) {
            $name = $character->getName();

            $io->info("Create account for {$name} ...");

            try {
                $tryToLoginRequest = $this->httpClient->request(
                    'PUT',
                    "{$this->identityServerUrl}/api/v1/auth/self",
                    ['json' => ['login' => $name, 'password' => '31323334353637383931']]
                );
                $result = [];
                parse_str($tryToLoginRequest->getHeaders()['set-cookie'][0], $result);
                $sid = explode(';', $result['sid'])[0];
                $io->warning("{$name} as already an account !");

                continue;
            } catch (\Exception $e) {
                $createETUserResponse = $this->httpClient->request(
                    'POST',
                    "{$this->identityServerUrl}/api/v1/users",
                    ['json' => ['username' => "{$name}", 'display_name' => "{$name}", 'password' => '31323334353637383931']]
                );
                $statusCode = $createETUserResponse->getStatusCode();
                if ($statusCode === 200) {
                    $io->info("Account created for {$name}");
                } else {
                    $io->error("Error while creating account for {$name}");
                }
            }
        }

        $io->info('Create accounts done.');

        return Command::SUCCESS;
    }
}
