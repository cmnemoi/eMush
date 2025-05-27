<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Mush\User\Entity\User;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];

    public function findAll(): array
    {
        return $this->users;
    }

    public function save(User $user): void
    {
        $this->users[] = $user;
    }
}
