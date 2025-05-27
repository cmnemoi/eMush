<?php

namespace Mush\User\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\User\Entity\User;

interface UserServiceInterface
{
    public function persist(User $user): User;

    public function findById(int $id): ?User;

    public function findUserByUserId(string $userId): ?User;

    public function createUser(string $userId, string $username): User;

    public function findUserByNonceCode(string $nonceCode): ?User;

    public function findUserDaedaluses(User $user): array;

    public function findUserClosedPlayers(User $user): ArrayCollection;

    public function acceptRules(User $user): void;

    public function readLatestNews(User $user): void;
}
