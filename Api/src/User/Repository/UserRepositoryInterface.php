<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Mush\User\Entity\User;

interface UserRepositoryInterface
{
    /**
     * @return User[]
     */
    public function findAll(): array;

    public function save(User $user): void;
}
