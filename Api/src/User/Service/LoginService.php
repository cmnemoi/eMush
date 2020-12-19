<?php

namespace Mush\User\Service;

use Etwin\OauthClient\AccessToken;
use Etwin\OauthClient\RfcOauthClient;
use GuzzleHttp\Exception\GuzzleException;

class LoginService
{
    private RfcOauthClient $oauthClient;

    public function __construct(
        string $authorizeUri,
        string $tokenUri,
        string $clientId,
        string $clientSecret
    ) {
        $this->oauthClient = new RfcOauthClient(
            $authorizeUri,
            $tokenUri,
            'http://localhost:8080/api/v1/login/callback',
            $clientId,
            $clientSecret,
        );
    }


    public function login(string $code): AccessToken
    {
        try {
            $accessToken = $this->oauthClient->getAccessTokenSync($code);
        } catch (GuzzleException $e) {
            dump($e);
            throw $e; //@TODO
        } catch (\JsonException $e) {
            throw $e; //@TODO
        }

        return $accessToken;
    }

    public function getAuthorizationUri(string $scope, string $state): string
    {
        return $this->oauthClient->getAuthorizationUri($scope, $state);
    }
}