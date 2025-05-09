<?php

namespace Mush\Game\Command;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\CharacterConfigRepository;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Service\LoginService;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
#[AsCommand(
    name: 'mush:fill-daedalus',
    description: 'Fill a new Daedalus',
    hidden: false
)]
class FillDaedalusCommand extends Command
{
    private const string OPTION_NUMBER = 'number';
    private const string OPTION_DAEDALUS_ID = 'daedalus_id';
    private const string OPTION_DAEDALUS_LOCALE = 'daedalus_locale';
    private HttpClientInterface $httpClient;
    private CharacterConfigRepository $characterConfigRepository;
    private DaedalusRepository $daedalusRepository;
    private DaedalusServiceInterface $daedalusService;
    private LoginService $loginService;
    private PlayerServiceInterface $playerService;
    private string $identityServerUri;
    private string $oAuthCallback;
    private string $oAuthClientId;

    public function __construct(
        HttpClientInterface $httpClient,
        CharacterConfigRepository $characterConfigRepository,
        DaedalusRepository $daedalusRepository,
        DaedalusServiceInterface $daedalusService,
        LoginService $loginService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct();
        $this->httpClient = $httpClient;
        $this->characterConfigRepository = $characterConfigRepository;
        $this->daedalusRepository = $daedalusRepository;
        $this->daedalusService = $daedalusService;
        $this->loginService = $loginService;
        $this->playerService = $playerService;
        $this->identityServerUri = $_ENV['IDENTITY_SERVER_URI'];
        $this->oAuthCallback = $_ENV['OAUTH_CALLBACK'];
        $this->oAuthClientId = $_ENV['OAUTH_CLIENT_ID'];
    }

    public function isntAvailable(string $name, Daedalus $daedalus): bool
    {
        $availableCharacters = [];
        foreach ($this->daedalusService->findAvailableCharacterForDaedalus($daedalus) as $character) {
            $availableCharacters[] = $character->getName();
        }

        return !\in_array($name, $availableCharacters, true);
    }

    protected function configure(): void
    {
        $this->addOption($this::OPTION_NUMBER, null, InputOption::VALUE_OPTIONAL, 'Number of member to board ?', 16);
        $this->addOption($this::OPTION_DAEDALUS_ID, null, InputOption::VALUE_OPTIONAL, 'Daedalus id ?', null);
        $this->addOption($this::OPTION_DAEDALUS_LOCALE, null, InputOption::VALUE_REQUIRED, 'Daedalus locale ?', 'fr');
    }

    /**
     * @psalm-suppress TypeDoesNotContainNull
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $numberOfMemberToBoard = $input->getOption($this::OPTION_NUMBER);
        if ($numberOfMemberToBoard < 1 || $numberOfMemberToBoard > 16) {
            $io->error($this::OPTION_NUMBER . ' should be between 1 and 16');

            return Command::INVALID;
        }

        $locale = $input->getOption($this::OPTION_DAEDALUS_LOCALE);
        $daedalusId = $input->getOption($this::OPTION_DAEDALUS_ID);

        $io->info("{$numberOfMemberToBoard} character will be added to daedalus {$daedalusId}");

        $io->title('Filling Daedalus...');

        /** @var CharacterConfig[] $allCharacter */
        $allCharacter = $this->characterConfigRepository->findAll();

        $count = 0;

        if ($daedalusId === null) {
            if ($locale !== 'fr' && $locale !== 'en') {
                $io->error("locale must be fr or en. Found : {$locale}");

                return Command::FAILURE;
            }
            $daedalus = $this->daedalusService->findAvailableDaedalusInLanguage($locale);
            if ($daedalus === null) {
                $io->error("Can't find any available daedalus for {$locale}.");

                return Command::FAILURE;
            }
            $daedalusId = $daedalus->getId();
        } else {
            $daedalus = $this->daedalusRepository->find($daedalusId);
            if ($daedalus === null) {
                $io->error("Can't fin daedalus with id {$daedalusId} !");

                return Command::FAILURE;
            }
        }
        foreach ($allCharacter as $character) {
            $name = $character->getName();

            $io->info($name . ' on boarding ...');

            if ($this->isntAvailable($name, $daedalus)) {
                $io->info("{$name} not available, skipping ...");

                continue;
            }

            try {
                $tryToLoginRequest = $this->httpClient->request(
                    'PUT',
                    "{$this->identityServerUri}/api/v1/auth/self",
                    ['json' => ['login' => $name, 'password' => '31323334353637383931']]
                );
                $result = [];
                parse_str($tryToLoginRequest->getHeaders()['set-cookie'][0], $result);

                /** @var string $allCharacter */
                $sid = explode(';', $result['sid'])[0];

                $client = HttpClient::create([
                    'headers' => [
                        'Cookie' => new Cookie('sid', $sid),
                    ],
                ]);
                $getTokenETResponse = $client->request(
                    'GET',
                    "{$this->identityServerUri}/oauth/authorize?access_type=offline&response_type=code&redirect_uri={$this->oAuthCallback}&client_id={$this->oAuthClientId}&scope=base&state=http://localhost:8081/token",
                    ['max_redirects' => 0]
                );
                $location = $getTokenETResponse->getHeaders(false)['location'];
                $queryResult = [];

                $url = parse_url($location[0]);
                if ($url === null) {
                    $io->warning("{$name} cannot join Daedalus : Cannot retrieve url or redirect from ET response for authorization token. Skipping ...");

                    continue;
                }
                $query = $url['query'] ?? null;
                if ($query === null) {
                    $io->warning("{$name} cannot join Daedalus. : Cannot retrieve query part from url from ET response for authorization token. Skipping ...");

                    continue;
                }
                parse_str($query, $queryResult);

                $tokenET = $queryResult['code'];
                $fistTokenApi = $this->loginService->verifyCode($tokenET);

                $user = $this->loginService->login($fistTokenApi);

                $player = $this->playerService->createPlayer($daedalus, $user, $name);
                ++$count;
                $io->info($name . ' joined Daedalus ' . $daedalusId . '!');
            } catch (\Exception $e) {
                $trace = $e->getTraceAsString();
                $message = $e->getMessage();
                $io->warning("{$name} cannot join Daedalus. Error while joining daedalus : {$message} -> {$trace}");

                continue;
            }

            if ($count === $numberOfMemberToBoard) {
                break;
            }
        }
        $io->info("{$count} member joined the Daedalus.");

        return Command::SUCCESS;
    }
}
