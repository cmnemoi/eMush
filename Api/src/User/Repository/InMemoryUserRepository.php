<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Mush\User\Entity\User;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];

    public function findOneByIdOrThrow(int $id): User
    {
        throw new \LogicException('Not implemented');
    }

    public function findAll(): array
    {
        return $this->users;
    }

    public function findUserPastCyclesCount(User $user): int
    {
        throw new \LogicException('Not implemented');
    }

    public function findUserNumberOfPastGames(User $user): int
    {
        throw new \LogicException('Not implemented');
    }

    public function save(User $user): void
    {
        $this->users[] = $user;
    }
}
