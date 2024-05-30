<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface PlayerInfoRepositoryInterface
{
    public function getCurrentPlayerInfoForUserOrNull(User $user): ?PlayerInfo;
}
