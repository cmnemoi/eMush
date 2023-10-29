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
        $legacyUser->setAvailableExperience($mushProfileResponse->xp);
        $legacyUser->setHistoryHeroes($mushProfileResponse->historyHeroes);
        $legacyUser->setHistoryShips($mushProfileResponse->historyShips);

        // mush data from scraping
        $mushProfile = $this->scrapMushProfile($serverUrl, $sid);
        $legacyUser->setCharacterLevels($mushProfile['characterLevels']);
        $legacyUser->setSkins($mushProfile['skins']);
        $legacyUser->setFlairs($mushProfile['flairs']);
        $legacyUser->setKlix($mushProfile['klix']);
        $legacyUser->setExperienceResetKlixCost($mushProfile['experienceResetKlixCost']);

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

    private function scrapMushProfile(string $serverUrl, string $sid): array
    {
        $htmlContent = $this->get($serverUrl . 'me', $this->getCookieFromServerAndSid($serverUrl, $sid));
        $crawler = new Crawler($htmlContent);

        return [
            'characterLevels' => $this->getUserCharacterLevels($crawler),
            'skins' => $this->getUserSkins($crawler),
            'flairs' => $this->getUserFlairs($crawler),
            'klix' => $this->getUserKlix($crawler),
            'experienceResetKlixCost' => $this->getUserExperienceResetKlixCost($crawler),
        ];
    }

    private function getUserCharacterLevels(Crawler $crawler): array
    {
        $characters = [];
        $skinDivs = $crawler->filter('.level');
        if ($skinDivs->count() === 0) {
            throw new \Exception('Impossible to find your Mush character levels');
        }
        $characterNames = $skinDivs->each(function (Crawler $node, $i) {
            return explode(' ', $node->ancestors()->first()->text())[1];
        });
        $characterLevels = $skinDivs->each(function (Crawler $node, $i) {
            return intval($node->text());
        });

        foreach ($characterNames as $key => $characterName) {
            $characters[$characterName] = $characterLevels[$key];
        }

        return $characters;
    }

    private function getUserSkins(Crawler $crawler): array
    {
        $skinsStyleMap = [
            'background-position : 0px 	-1512px !important;' => 'jin_su_gangnam_style',
            'background-position : 0px 	-1604px !important;' => 'jin_su_vampire',
            'background-position : 0px 	-2063px !important;' => 'frieda',
            'background-position : 0px 	-1875px !important;' => 'kuan_ti',
            'background-position : 0px 	-1185px !important;' => 'janice',
            'background-position : 0px 	-1056px !important;' => 'roland',
            'background-position : 0px 	-1554px !important;' => 'hua',
            'background-position : 0px 	-1728px !important;' => 'paola',
            'background-position : 0px 	-1282px !important;' => 'chao',
            'background-position : 0px 	-1921px !important;' => 'finola',
            'background-position : 0px 	-1681px !important;' => 'stephen',
            'background-position : 0px 	-1233px !important;' => 'ian',
            'background-position : 0px 	-2017px !important;' => 'chun',
            'background-position : 0px 	-1391px !important;' => 'raluca',
            'background-position : 0px 	-1970px !important;' => 'gioele',
            'background-position : 0px 	-1335px !important;' => 'eleesha',
            'background-position : 0px 	-1444px !important;' => 'terrence',
        ];

        $skins = [];
        $skinDivs = $crawler->filter('div > .inl-blck');
        if ($skinDivs->count() === 0) {
            return $skins;
        }

        $skinDivs->each(function (Crawler $node, $i) use (&$skins, $skinsStyleMap) {
            $skinStyle = $node->attr('style');
            if ($skinStyle && array_key_exists($skinStyle, $skinsStyleMap)) {
                $skins[] = $skinsStyleMap[$skinStyle];
            }
        });

        return $skins;
    }

    private function getUserFlairs(Crawler $crawler): array
    {
        $flairs = [];
        $flairInputs = $crawler->filter("input[onclick=' return Main.onClickVanity( $(this) ); ']");
        if ($flairInputs->count() === 0) {
            return $flairs;
        }

        $flairInputs->each(function (Crawler $node, $i) use (&$flairs) {
            // get flair from strings like "Activer : Innocence Incarnée"
            $flairs[] = trim(explode(':', $node->ancestors()->first()->text())[1]);
        });

        return $flairs;
    }

    private function getUserKlix(Crawler $crawler): int
    {
        $klixImg = $crawler->filter('.klix');
        if ($klixImg->count() === 0) {
            throw new \Exception('Impossible to find your klix');
        }

        // looks like "I have 15 klix"
        $klixSentence = $klixImg->ancestors()->first()->text();
        // get only integers from the string
        $klixAmount = preg_replace('/[^0-9]/', '', $klixSentence);

        return intval($klixAmount);
    }

    private function getUserExperienceResetKlixCost(Crawler $crawler): int
    {
        $experienceResetKlixCost = $crawler->filter("a[href='/u/resetProgression/0']");
        if ($experienceResetKlixCost->count() === 0) {
            return 0;
        }

        // looks like "Redémarrer 30"
        $experienceResetKlixCostSentence = $experienceResetKlixCost->first()->text();
        // get only integers from the string
        $experienceResetKlixCostAmount = preg_replace('/[^0-9]/', '', $experienceResetKlixCostSentence);

        return intval($experienceResetKlixCostAmount);
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
        $clientSecret = $this->adminService->findSecretByName('TWINOID_IMPORT_CLIENT_SECRET')?->getValue();
        if (!$clientSecret) {
            throw new \Exception('TWINOID_IMPORT_CLIENT_SECRET secret is missing');
        }

        $uri = TwinoidURLEnum::TWINOID_TOKEN . '?' . http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
            'client_id' => $this->getClientId(),
            'client_secret' => $clientSecret,
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
