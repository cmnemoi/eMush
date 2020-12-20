<?php

namespace Mush\User\Service;

use Etwin\Auth\AccessTokenAuthContext;
use Etwin\Client\Auth;
use Etwin\Client\HttpEtwinClient;
use Etwin\OauthClient\RfcOauthClient;
use GuzzleHttp\Exception\GuzzleException;
use Mush\User\Entity\User;

class LoginService
{
    private UserServiceInterface $userService;
    private RfcOauthClient $oauthClient;
    private string $etwinUri;

    public function __construct(
        string $etwinUri,
        string $authorizeUri,
        string $tokenUri,
        string $oauthCallback,
        string $clientId,
        string $clientSecret,
        UserServiceInterface $userService
    ) {
        $this->userService = $userService;
        $this->etwinUri = $etwinUri;
        $this->oauthClient = new RfcOauthClient(
            $authorizeUri,
            $tokenUri,
            $oauthCallback,
            $clientId,
            $clientSecret,
        );
    }

    public function login(string $code): User
    {
        try {
            $accessToken = $this->oauthClient->getAccessTokenSync($code);
        } catch (GuzzleException $e) {
            dump($e);
            throw $e; //@TODO
        } catch (\JsonException $e) {
            throw $e; //@TODO
        }

        $apiClient = new HttpEtwinClient($this->etwinUri);
        $apiUser = $apiClient->getSelf(Auth::fromToken($accessToken->getAccessToken()));

        if ($apiUser instanceof AccessTokenAuthContext) {
            $userId = $apiUser->getUser()->getId()->getInner()->toString();
            $user = $this->userService->findUserByUserId($userId);

            if ($user === null) {
                $username = $apiUser->getUser()->getDisplayName()->getCurrent()->getInner()->toString();
                $user = $this->userService->createUser($userId, $username);
            }
        } else {
            throw new \LogicException('Auth context not supported');
        }

        return $user;
    }

    public function getAuthorizationUri(string $scope, string $state): string
    {
        return $this->oauthClient->getAuthorizationUri($scope, $state);
    }
}
