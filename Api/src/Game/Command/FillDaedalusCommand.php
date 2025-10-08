<?php

declare(strict_types=1);

namespace Mush\Game\Command;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\CharacterConfigRepository;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Service\LoginService;
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
final class FillDaedalusCommand extends Command
{
    private const int DEFAULT_MEMBERS_TO_BOARD = 16;
    private const int MIN_MEMBERS_TO_BOARD = 1;
    private const int MAX_MEMBERS_TO_BOARD = 16;
    private const string OPTION_NUMBER = 'number';
    private const string OPTION_DAEDALUS_ID = 'daedalus-id';
    private const string OPTION_DAEDALUS_LOCALE = 'daedalus-locale';
    private const string DEFAULT_PASSWORD_HEX = '31323334353637383931';
    private const string OAUTH_SCOPE = 'base';
    private const string STATE_REDIRECT = 'http://localhost:8081/token';
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

    protected function configure(): void
    {
        $this->addOption(self::OPTION_NUMBER, null, InputOption::VALUE_OPTIONAL, 'Number of members to board', self::DEFAULT_MEMBERS_TO_BOARD);
        $this->addOption(self::OPTION_DAEDALUS_ID, null, InputOption::VALUE_OPTIONAL, 'Daedalus id', null);
        $this->addOption(self::OPTION_DAEDALUS_LOCALE, null, InputOption::VALUE_REQUIRED, 'Daedalus locale', 'fr');
    }

    /**
     * @psalm-suppress TypeDoesNotContainNull
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Filling Daedalus...');

        $membersToBoard = $this->getMembersToBoard($input, $io);
        if ($membersToBoard === null) {
            return Command::INVALID;
        }

        $daedalus = $this->getDaedalusToFill($input, $io);
        if ($daedalus === null) {
            return Command::FAILURE;
        }

        /** @var CharacterConfig[] $characterConfigs */
        $characterConfigs = $this->characterConfigRepository->findAll();
        $boardedCount = $this->boardCharacters($characterConfigs, $daedalus, $membersToBoard, $io);
        $io->info("{$boardedCount} member(s) joined the Daedalus.");

        return Command::SUCCESS;
    }

    private function isCharacterAvailable(string $characterName, Daedalus $daedalus): bool
    {
        $availableCharacterNames = [];
        foreach ($this->daedalusService->findAvailableCharacterForDaedalus($daedalus) as $characterConfig) {
            $availableCharacterNames[] = $characterConfig->getName();
        }

        return \in_array($characterName, $availableCharacterNames, true);
    }

    private function isInvalidMembersCount(int $count): bool
    {
        return $count < self::MIN_MEMBERS_TO_BOARD || $count > self::MAX_MEMBERS_TO_BOARD;
    }

    private function resolveDaedalus(?string $daedalusId, string $locale): Daedalus
    {
        if ($daedalusId === null) {
            return $this->resolveDaedalusByLocale($locale);
        }

        return $this->resolveDaedalusById($daedalusId);
    }

    private function boardCharacters(array $characterConfigs, Daedalus $daedalus, int $limit, SymfonyStyle $io): int
    {
        $boardedCount = 0;

        foreach ($characterConfigs as $characterConfig) {
            $boardedCount += $this->handleCharacterBoarding($characterConfig, $daedalus, $io);
            if ($this->shouldStopBoarding($boardedCount, $limit)) {
                break;
            }
        }

        return $boardedCount;
    }

    private function onboardCharacter(string $characterName, Daedalus $daedalus): void
    {
        $sessionId = $this->authenticateAndGetSessionId($characterName);
        $authorizationCode = $this->fetchAuthorizationCode($sessionId);
        $firstToken = $this->loginService->verifyCode($authorizationCode);
        $user = $this->loginService->login($firstToken, ip: $this->getClientIp());
        $this->playerService->createPlayer($daedalus, $user, $characterName);
    }

    private function authenticateAndGetSessionId(string $characterName): string
    {
        $response = $this->httpClient->request(
            'PUT',
            "{$this->identityServerUri}/api/v1/auth/self",
            ['json' => ['login' => $characterName, 'password' => self::DEFAULT_PASSWORD_HEX]]
        );

        $headers = $response->getHeaders();
        if (!isset($headers['set-cookie'][0])) {
            throw new \RuntimeException('Missing set-cookie header on authentication response');
        }

        return $this->extractSessionIdFromSetCookie($headers['set-cookie'][0]);
    }

    private function extractSessionIdFromSetCookie(string $setCookieHeader): string
    {
        $parsed = [];
        parse_str($setCookieHeader, $parsed);
        if (!isset($parsed['sid'])) {
            throw new \RuntimeException('No session id present in authentication cookie');
        }

        $sessionParts = explode(';', $parsed['sid']);
        $sessionId = $sessionParts[0] ?? '';
        if ($sessionId === '') {
            throw new \RuntimeException('Empty session id extracted from authentication cookie');
        }

        return $sessionId;
    }

    private function fetchAuthorizationCode(string $sessionId): string
    {
        $client = HttpClient::create([
            'headers' => [
                'Cookie' => 'sid=' . $sessionId,
            ],
        ]);

        $response = $client->request(
            'GET',
            $this->buildAuthorizeUrl(),
            ['max_redirects' => 0]
        );

        $location = $this->getFirstRedirectLocation($response);

        return $this->extractAuthorizationCodeFromRedirect($location);
    }

    private function buildAuthorizeUrl(): string
    {
        $params = http_build_query([
            'access_type' => 'offline',
            'response_type' => 'code',
            'redirect_uri' => $this->oAuthCallback,
            'client_id' => $this->oAuthClientId,
            'scope' => self::OAUTH_SCOPE,
            'state' => self::STATE_REDIRECT,
        ]);

        return $this->identityServerUri . '/oauth/authorize?' . $params;
    }

    private function getMembersToBoard(InputInterface $input, SymfonyStyle $io): ?int
    {
        $membersToBoard = (int) $input->getOption(self::OPTION_NUMBER);
        if ($this->isInvalidMembersCount($membersToBoard)) {
            $io->error(self::OPTION_NUMBER . ' should be between ' . self::MIN_MEMBERS_TO_BOARD . ' and ' . self::MAX_MEMBERS_TO_BOARD);

            return null;
        }

        return $membersToBoard;
    }

    private function getDaedalusToFill(InputInterface $input, SymfonyStyle $io): ?Daedalus
    {
        $locale = (string) $input->getOption(self::OPTION_DAEDALUS_LOCALE);
        $daedalusId = $input->getOption(self::OPTION_DAEDALUS_ID);
        $io->info(((int) $input->getOption(self::OPTION_NUMBER)) . " characters will be added to daedalus {$daedalusId}");

        try {
            return $this->resolveDaedalus($daedalusId, $locale);
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return null;
        }
    }

    private function resolveDaedalusByLocale(string $locale): Daedalus
    {
        if (!\in_array($locale, LanguageEnum::getAll(), true)) {
            throw new \InvalidArgumentException('Locale must be one of: ' . implode(', ', LanguageEnum::getAll()) . ". Found: {$locale}");
        }

        $daedalus = $this->daedalusService->findAvailableDaedalusInLanguage($locale);
        if ($daedalus === null) {
            throw new \RuntimeException("Can't find any available daedalus for {$locale}.");
        }

        return $daedalus;
    }

    private function resolveDaedalusById(string $daedalusId): Daedalus
    {
        $daedalus = $this->daedalusRepository->find($daedalusId);
        if ($daedalus === null) {
            throw new \RuntimeException("Can't find daedalus with id {$daedalusId}.");
        }

        return $daedalus;
    }

    private function handleCharacterBoarding(CharacterConfig $characterConfig, Daedalus $daedalus, SymfonyStyle $io): int
    {
        $characterName = $characterConfig->getName();
        $io->info($characterName . ' onboarding...');

        if (!$this->isCharacterAvailable($characterName, $daedalus)) {
            $io->info("{$characterName} not available, skipping...");

            return 0;
        }

        try {
            $this->onboardCharacter($characterName, $daedalus);
            $io->info($characterName . ' joined Daedalus ' . $daedalus->getId() . '!');

            return 1;
        } catch (\Throwable $throwable) {
            $io->warning("{$characterName} cannot join Daedalus. Error: {$throwable->getMessage()}");

            return 0;
        }
    }

    private function shouldStopBoarding(int $boardedCount, int $limit): bool
    {
        return $boardedCount >= $limit;
    }

    private function getFirstRedirectLocation($response): string
    {
        $locations = $response->getHeaders(false)['location'] ?? null;
        if ($locations === null || !isset($locations[0])) {
            throw new \RuntimeException('Missing redirect location while fetching authorization code');
        }

        return $locations[0];
    }

    private function extractAuthorizationCodeFromRedirect(string $location): string
    {
        $url = parse_url($location);
        $query = $url['query'] ?? null;
        if ($query === null) {
            throw new \RuntimeException('Missing query string in authorization redirect');
        }

        parse_str($query, $queryResult);
        $authorizationCode = $queryResult['code'] ?? null;
        if (!\is_string($authorizationCode) || $authorizationCode === '') {
            throw new \RuntimeException('Missing authorization code in authorization redirect');
        }

        return $authorizationCode;
    }

    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        return $ip;
    }
}
