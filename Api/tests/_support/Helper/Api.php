<?php

namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\REST;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Mush\User\Entity\User;
use Lcobucci\JWT\Encoding\E;

class Api extends \Codeception\Module
{
    private REST $restModule;

    private JWSProviderInterface $encoder;

    private string $token;

    public function _before(TestInterface $test)
    {
        parent::_before($test);

        $restModule = $this->getModule('REST');
        $this->restModule = $restModule;

        /** @var Symfony $symfonyModule */
        $symfonyModule = $this->getModule('Symfony');
        $this->encoder = $symfonyModule->grabService(JWSProviderInterface::class);


    }


    public function getAuthToken(?User $user = null): string
    {
        if ($user) {
            $this->token = $this->encoder->create(['roles'=> $user->getRoles(), "userId" => $user->getUserId()])->getToken();
        }

        return $this->token;
    }

    public function sendPOSTRequest(string $url, array $data = [], bool $debug = false)
    {
        $this->addHeader();

        if ($debug) {
            $url .= "?XDEBUG_SESSION_START=PHPSTORM";
        }
        $this->restModule->sendPost($url, json_encode($data));
    }

    public function sendGETRequest(string $url, array $data = [], bool $debug = false)
    {
        $this->addHeader();

        if ($debug) {
            $url .= "?XDEBUG_SESSION_START=PHPSTORM";
        }
        $this->restModule->sendGet($url, $data);
    }

    protected function addHeader(bool $jsonHeader = true)
    {
        $this->restModule->haveHttpHeader('Content-Type', 'application/json');

        if ($jsonHeader) {
            $this->restModule->haveHttpHeader('Accept', 'application/json');
        }

        if ($this->token) {
            $this->restModule->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        }
    }
}
