<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Mush\User\Entity\User;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];

    public function findOneByIdOrThrow(int $id): User
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }

        throw new \RuntimeException("User with id {$id} not found");
    }

    public function findAll(): array
    {
        return $this->users;
    }

    public function findByUuids(array $uuids): array
    {
        return array_filter(
            $this->users,
            static fn (User $user) => \in_array($user->getUserId(), $uuids, true)
        );
    }

    public function findUserPastCyclesCount(User $user): int
    {
        throw new \LogicException('Not implemented');
    }

    public function findUserNumberOfPastGames(User $user): int
    {
        throw new \LogicException('Not implemented');
    }

    public function hasCompletedAGameBefore(User $user, \DateTime $date): bool
    {
        throw new \LogicException('Not implemented');
    }

    public function save(User $user): void
    {
        // Update if exists, otherwise add
        $found = false;
        foreach ($this->users as $index => $existingUser) {
            if ($existingUser->getId() === $user->getId()) {
                $this->users[$index] = $user;
                $found = true;

                break;
            }
        }

        if (!$found) {
            $this->users[] = $user;
        }
    }
}
