<?php

declare(strict_types=1);

namespace Mush\User\Service;

use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class TokenService
{
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    public function toUser(): User
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            throw new \RuntimeException('No token found');
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            throw new \RuntimeException('No user found from token');
        }

        return $user;
    }
}
