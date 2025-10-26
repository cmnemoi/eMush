<?php

namespace Mush\User\Service;

use Eternaltwin\Auth\AccessTokenAuthContext;
use Eternaltwin\Client\Auth;
use Eternaltwin\Client\HttpEtwinClient;
use Eternaltwin\OauthClient\RfcOauthClient;
use GuzzleHttp\Exception\GuzzleException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Mush\User\ValueObject\IpHash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoginService
{
    private UserServiceInterface $userService;
    private RfcOauthClient $oauthClient;
    private JWTEncoderInterface $jwtEncoder;
    private string $admin;
    private string $appEnv;
    private string $etwinUri;
    private string $appSecret;

    public function __construct(
        string $etwinUri,
        string $authorizeUri,
        string $tokenUri,
        string $oauthCallback,
        string $clientId,
        string $clientSecret,
        string $admin,
        string $appEnv,
        string $appSecret,
        UserServiceInterface $userService,
        JWTEncoderInterface $jwtEncoder,
    ) {
        $this->userService = $userService;
        $this->etwinUri = $etwinUri;
        $this->jwtEncoder = $jwtEncoder;
        $this->admin = $admin;
        $this->appEnv = $appEnv;
        $this->appSecret = $appSecret;
        $this->oauthClient = new RfcOauthClient(
            $authorizeUri,
            $tokenUri,
            $oauthCallback,
            $clientId,
            $clientSecret,
        );
    }

    public function login(string $codeToken, string $ip): User
    {
        try {
            $decodedToken = $this->jwtEncoder->decode($codeToken);
        } catch (JWTDecodeFailureException $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        if (!$decodedToken || !($code = $decodedToken['code'])) {
            throw new UnauthorizedHttpException('Bad Credentials');
        }

        $user = $this->userService->findUserByNonceCode($code);

        if ($user === null) {
            throw new UnauthorizedHttpException('Bad Credentials');
        }

        $user
            ->setNonceCode(null)
            ->setNonceExpiryDate(null);
        if ($user->getUserId() === $this->admin) {
            $user->setRoles([RoleEnum::SUPER_ADMIN]);
        }

        if ($this->appEnv === 'dev') {
            $user->setRoles([RoleEnum::SUPER_ADMIN]);
            $user->acceptRules();
            $user->readLatestNews();
        }

        $user->addHashedIp(IpHash::hashFor($ip, $this->appSecret));

        $this->userService->persist($user);

        return $user;
    }

    public function verifyCode(string $code): string
    {
        try {
            $accessToken = $this->oauthClient->getAccessTokenSync($code);
        } catch (GuzzleException $e) {
            throw $e;
        } catch (\JsonException $e) {
            throw $e;
        }

        $apiClient = new HttpEtwinClient($this->etwinUri);
        $apiUser = $apiClient->getSelf(Auth::fromToken($accessToken->getAccessToken()));

        if (!$apiUser instanceof AccessTokenAuthContext) {
            throw new \LogicException('Auth context not supported');
        }

        $userId = $apiUser->getUser()->getId()->getInner()->toString();
        $user = $this->userService->findUserByUserId($userId);

        if ($user === null) {
            $username = $apiUser->getUser()->getDisplayName()->getCurrent()->getValue()->toString();
            $user = $this->userService->createUser($userId, $username);
        }

        if ($user->getUsername() !== $apiUser->getUser()->getDisplayName()->getCurrent()->getValue()->toString()) {
            $user->setUsername($apiUser->getUser()->getDisplayName()->getCurrent()->getValue()->toString());
        }

        $nonce = bin2hex(openssl_random_pseudo_bytes(10)); // 20 chars

        $expiryTime = time() + 60; // 1 minute expiration

        $user
            ->setNonceCode($nonce)
            ->setNonceExpiryDate((new \DateTime())->setTimestamp($expiryTime));

        $this->userService->persist($user);

        return $this->jwtEncoder
            ->encode(
                [
                    'code' => $nonce,
                    'exp' => $expiryTime,
                ]
            );
    }

    public function getAuthorizationUri(string $scope, string $csrfState): string
    {
        return (string) $this->oauthClient->getAuthorizationUri($scope, $csrfState);
    }

    public function encodeOAuthState(string $csrfState, string $frontendRedirectUri): string
    {
        $json = json_encode(['csrf' => $csrfState, 'redirect' => $frontendRedirectUri]);
        if (!$json) {
            throw new \RuntimeException('Failed to encode OAuth state');
        }

        return base64_encode($json);
    }

    /**
     * @return array{csrf: string, redirect: string}
     */
    public function decodeOAuthState(string $encodedState): array
    {
        $decoded = base64_decode($encodedState, true);
        if (!$decoded) {
            throw new UnauthorizedHttpException('Bad credentials: invalid state parameter');
        }

        $stateData = json_decode($decoded, true);

        if (
            !\is_array($stateData)
            || !isset($stateData['csrf'])
            || !isset($stateData['redirect'])
            || !\is_string($stateData['csrf'])
            || !\is_string($stateData['redirect'])
        ) {
            throw new UnauthorizedHttpException('Bad credentials: invalid state parameter');
        }

        return [
            'csrf' => $stateData['csrf'],
            'redirect' => $stateData['redirect'],
        ];
    }

    public function buildFrontendCallbackUrl(string $frontendRedirectUri, string $token, string $csrfState): string
    {
        $parameters = http_build_query(['code' => $token, 'state' => $csrfState]);

        return $frontendRedirectUri . '?' . $parameters;
    }
}
