<?php

declare(strict_types=1);

namespace Mush\Tests\unit\MetaGame\TestDoubles;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class InMemoryTokenStorage implements TokenStorageInterface
{
    private UsernamePasswordToken $token;

    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }
}
