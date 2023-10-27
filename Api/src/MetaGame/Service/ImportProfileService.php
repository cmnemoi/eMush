<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\MetaGame\Enum\TwinoidAPIFieldsEnum;
use Mush\MetaGame\Enum\TwinoidURLEnum;
use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\LegacyUserTwinoidProfile;
use Mush\User\Entity\User;
use Mush\User\Service\LegacyUserServiceInterface;
use Mush\User\Service\LegacyUserTwinoidProfileServiceInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

final class ImportProfileService
{   
    private string $appEnv;
    private AdminServiceInterface $adminService;
    private EntityManagerInterface $entityManager;
    private LegacyUserServiceInterface $legacyUserService;
    private LegacyUserTwinoidProfileServiceInterface $legacyUserTwinoidProfileService;
    private HttpBrowser $client;

    public function __construct(
        string $appEnv,
        AdminServiceInterface $adminService,
        EntityManagerInterface $entityManager,
        LegacyUserServiceInterface $legacyUserService,
        LegacyUserTwinoidProfileServiceInterface $legacyUserTwinoidProfileService
    ) {
        $this->appEnv = $appEnv;
        $this->adminService = $adminService;
        $this->entityManager = $entityManager;
        $this->legacyUserService = $legacyUserService;
        $this->legacyUserTwinoidProfileService = $legacyUserTwinoidProfileService;
        $this->client = new HttpBrowser(HttpClient::create());
    }

    public function saveLegacyUser(User $user, string $serverUrl, string $sid, string $code): LegacyUser
    {
        $legacyUser = $this->legacyUserService->findByUser($user);
        if (!$legacyUser instanceof LegacyUser) {
            $legacyUser = new LegacyUser($user);
        }

        // twinoid data from API
        $twinoidToken = $this->getTwinoidAPIToken($code);
        $legacyUser->setTwinoidProfile($this->getTwinoidProfile($legacyUser, $serverUrl, $twinoidToken));

        // mush data from API
        $mushProfileResponse = json_decode($this->get(
            url: $this->buildMushApiMeUrl(
                $twinoidToken,
                $serverUrl,
                TwinoidAPIFieldsEnum::buildMushUserFields()
            ),
        ));
        $legacyUser->setHistoryHeroes($mushProfileResponse->historyHeroes);
        $legacyUser->setHistoryShips($mushProfileResponse->historyShips);

        // mush data from scraping
        $htmlContent = $this->get($serverUrl . 'me', $this->getCookieFromServerAndSid($serverUrl, $sid));
        $crawler = new Crawler($htmlContent);
        $legacyUser->setCharacterLevels($this->getUserCharacterLevels($crawler));

        $this->entityManager->persist($legacyUser);
        $this->entityManager->flush();

        return $legacyUser;
    }

    private function getTwinoidProfile(LegacyUser $user, string $serverUrl, string $token): LegacyUserTwinoidProfile
    {   
        $twinoidProfile = $this->legacyUserTwinoidProfileService->findByLegacyUser($user);
        if (!$twinoidProfile instanceof LegacyUserTwinoidProfile) {
            $twinoidProfile = new LegacyUserTwinoidProfile();
        }

        $twinoidProfileResponse = json_decode($this->get(
            url: $this->buildTwinoidApiUserUri(
                $token,
                fields: TwinoidAPIFieldsEnum::buildTwinoidUserFields(),
            )
        ));

        $twinoidProfile->setTwinoidId($twinoidProfileResponse->id);
        $twinoidProfile->setTwinoidUsername($twinoidProfileResponse->name);

        $sites = new ArrayCollection($twinoidProfileResponse->sites);
        $mushSite = $sites->filter(fn ($site) => $site->site->id === TwinoidURLEnum::getMushServerSiteIDFromName($serverUrl));
        if ($mushSite->isEmpty()) {
            throw new \Exception('Impossible to find your Mush achievements. Have you played Mush?');
        }

        $twinoidProfile->setStats($mushSite->first()->stats);
        $twinoidProfile->setAchievements($mushSite->first()->achievements);

        $this->entityManager->persist($twinoidProfile);

        return $twinoidProfile;
    }

    private function getCookieFromServerAndSid(string $serverUrl, string $sid): Cookie
    {
        return match ($serverUrl) {
            TwinoidURLEnum::MUSH_VG => new Cookie('sid', $sid),
            TwinoidURLEnum::MUSH_TWINOID_COM => new Cookie('mush_sid', $sid),
            TwinoidURLEnum::MUSH_TWINOID_ES => new Cookie('sid', $sid),
            default => throw new \Exception('This Mush server doesn\'t exist'),
        };
    }

    private function getUserCharacterLevels(Crawler $crawler): array
    {
        $characters = [];
        $characterDivs = $crawler->filter('.level');
        if ($characterDivs->count() === 0) {
            throw new \Exception('Impossible to find your Mush character levels');
        }
        $characterNames = $characterDivs->each(function (Crawler $node, $i) {
            return explode(' ', $node->ancestors()->first()->text())[1];
        });
        $characterLevels = $characterDivs->each(function (Crawler $node, $i) {
            return intval($node->text());
        });

        foreach ($characterNames as $key => $characterName) {
            $characters[$characterName] = $characterLevels[$key];
        }

        return $characters;
    }

    private function getTwinoidAPIToken(string $code): string
    {
        $this->client->request(
            method: 'POST',
            uri: $this->buildTwinoidApiTokenUrl($code),
        );

        $token = $this->client->getResponse()->getContent();

        return json_decode($token)->access_token;
    }

    private function buildTwinoidApiTokenUrl(string $code): string
    {
        $uri = TwinoidURLEnum::TWINOID_TOKEN . '?' . http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
            'client_id' => $this->getClientId(),
            'client_secret' => $this->adminService->findSecretByName('TWINOID_IMPORT_CLIENT_SECRET')->getValue(),
        ]);

        return $uri;
    }

    private function buildMushApiMeUrl(string $token, string $server, string $fields): string
    {
        $uri = $server . 'tid/graph/me?' . http_build_query([
            'access_token' => $token,
            'fields' => $fields,
        ]);

        return $uri;
    }

    private function buildTwinoidApiUserUri(string $token, string $fields): string
    {
        $uri = TwinoidURLEnum::TWINOID_API_ME_ENDPOINT . '?' . http_build_query([
            'access_token' => $token,
            'fields' => $fields,
        ]);

        return $uri;
    }

    private function get(string $url, Cookie $cookie = null): string
    {
        $this->client->request(
            method: 'GET',
            uri: $url,
            server: $cookie ? ['HTTP_COOKIE' => $cookie] : []
        );

        return $this->client->getResponse()->getContent();
    }

    private function getRedirectUri(): string
    {
        return match ($this->appEnv) {
            'dev' => 'http://localhost/import',
            'emush.staging' => 'https://staging.emush.eternaltwin.org/import',
            'emush.production' => 'https://emush.eternaltwin.org/import',
            default => throw new \Exception('This environment doesn\'t exist'),
        };
    }

    private function getClientId(): int
    {
        return match ($this->appEnv) {
            'dev' => 407,
            'emush.staging' => 429,
            'emush.production' => 430,
            default => throw new \Exception('This environment doesn\'t exist'),
        };
    }
}
