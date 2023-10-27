<?php

declare(strict_types=1);

namespace Mush\User\Service;

use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\User;

interface LegacyUserServiceInterface
{
    public function findByUser(User $user): ?LegacyUser;
}
