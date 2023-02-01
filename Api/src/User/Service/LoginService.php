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
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Psr\Log\LoggerInterface;

class LoginService
{
    private UserServiceInterface $userService;
    private RfcOauthClient $oauthClient;
    private JWTEncoderInterface $jwtEncoder;
    private LoggerInterface $logger;
    private string $admin;
    private string $appEnv;
    private string $etwinUri;

    public function __construct(
        string $etwinUri,
        string $authorizeUri,
        string $tokenUri,
        string $oauthCallback,
        string $clientId,
        string $clientSecret,
        string $admin,
        string $appEnv,
        UserServiceInterface $userService,
        JWTEncoderInterface $jwtEncoder,
        LoggerInterface $logger
    ) {
        $this->userService = $userService;
        $this->etwinUri = $etwinUri;
        $this->jwtEncoder = $jwtEncoder;
        $this->logger = $logger;
        $this->admin = $admin;
        $this->appEnv = $appEnv;
        $this->oauthClient = new RfcOauthClient(
            $authorizeUri,
            $tokenUri,
            $oauthCallback,
            $clientId,
            $clientSecret,
        );
    }

    public function login(string $codeToken): User
    {
        try {
            $decodedToken = $this->jwtEncoder->decode($codeToken);
        } catch (JWTDecodeFailureException $e) {
            $errorMessage = 'login: JWTDecodeFailureException: ' . $e->getMessage();
            $this->logger->error($errorMessage);
            throw new UnauthorizedHttpException($errorMessage);
        }

        if (!$decodedToken || !($code = $decodedToken['code'])) {
            $errorMessage = 'login: Bad Credentials';
            $this->logger->error($errorMessage);
            throw new UnauthorizedHttpException($errorMessage);
        }

        $user = $this->userService->findUserByNonceCode($code);

        if ($user === null) {
            $errorMessage = 'login: Bad Credentials';
            $this->logger->error($errorMessage);
            throw new UnauthorizedHttpException($errorMessage);
        }

        $user
            ->setNonceCode(null)
            ->setNonceExpiryDate(null)
        ;
        if ($user->getUserId() === $this->admin) {
            $user->setRoles([RoleEnum::SUPER_ADMIN]);
        }

        if ($this->appEnv === 'dev') {
            $user->setRoles([RoleEnum::SUPER_ADMIN]);
        }

        $this->userService->persist($user);

        return $user;
    }

    public function verifyCode(string $code): string
    {
        try {
            $accessToken = $this->oauthClient->getAccessTokenSync($code);
        } catch (GuzzleException $e) {
            $this->logger->error('verifyCode: GuzzleException: ' . $e->getMessage());
            throw new UnauthorizedHttpException($e->getMessage());
        } catch (\JsonException $e) {
            $this->logger->error('verifyCode: JsonException: ' . $e->getMessage());
            throw new UnauthorizedHttpException($e->getMessage());
        }

        $apiClient = new HttpEtwinClient($this->etwinUri);
        $apiUser = $apiClient->getSelf(Auth::fromToken($accessToken->getAccessToken()));

        if (!$apiUser instanceof AccessTokenAuthContext) {
            $errorMessage = 'verifyCode: Auth context not supported';
            $this->logger->error($errorMessage);
            throw new \LogicException($errorMessage);
        }

        $userId = $apiUser->getUser()->getId()->getInner()->toString();
        $user = $this->userService->findUserByUserId($userId);

        if ($user === null) {
            $username = $apiUser->getUser()->getDisplayName()->getCurrent()->getValue()->toString();
            $user = $this->userService->createUser($userId, $username);
        }

        $nonce = bin2hex(openssl_random_pseudo_bytes(10)); // 20 chars

        $expiryTime = time() + 60; // 1 minute expiration

        $user
            ->setNonceCode($nonce)
            ->setNonceExpiryDate((new \DateTime())->setTimestamp($expiryTime))
        ;

        $this->userService->persist($user);

        $token = $this->jwtEncoder
            ->encode([
                    'code' => $nonce,
                    'exp' => $expiryTime,
                ]
            );

        return $token;
    }

    public function getAuthorizationUri(string $scope, ?string $state): string
    {
        return (string) $this->oauthClient->getAuthorizationUri($scope, $state ?? '');
    }
}
