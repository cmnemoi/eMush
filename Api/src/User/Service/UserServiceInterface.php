<?php

namespace Mush\User\Service;

use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface UserServiceInterface
{
    public function persist(User $user): User;

    public function findById(int $id): ?User;

    public function findUsers(int $page, int $size, array $sort): array;

    public function findUserByUserId(string $userId): ?User;

    public function createUser(string $userId, string $username): User;

    public function findUserByNonceCode(string $nonceCode): ?User;

    public function findUserDaedaluses(User $user): array;

    public function findUserCurrentPlayer(User $user): ?PlayerInfo;
}
