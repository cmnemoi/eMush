<?php

declare(strict_types=1);

namespace Mush\User\Service;

use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\User;
use Mush\User\Repository\LegacyUserRepository;

final class LegacyUserService implements LegacyUserServiceInterface
{
    private LegacyUserRepository $legacyUserRepository;

    public function __construct(LegacyUserRepository $legacyUserRepository)
    {
        $this->legacyUserRepository = $legacyUserRepository;
    }

    public function findByUser(User $user): ?LegacyUser
    {
        return $this->legacyUserRepository->findOneByUser($user);
    }
}
