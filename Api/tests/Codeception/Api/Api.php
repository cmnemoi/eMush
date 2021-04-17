<?php


namespace Codeception\Api;

use Codeception\Module\REST;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Mush\User\Entity\User;

trait Api
{
    private $client;

    private REST $restModule;

    private LcobucciJWTEncoder $encoder;

    private string $token;

    private bool $debug;

    public function setEncoder(LcobucciJWTEncoder $encoder): Api
    {
        $this->encoder = $encoder;
        return $this;
    }

    public function setRestModule($restModule)
    {
        if (!$restModule instanceof REST) {
            throw new \InvalidArgumentException('not REST module');
        }

        $this->restModule = $restModule;
    }

}