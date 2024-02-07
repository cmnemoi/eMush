<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Player\Entity\Player;
use Mush\User\Entity\User;

interface ModerationServiceInterface
{
    public function banUser(User $user): User;

    public function unbanUser(User $user): User;

    public function quarantinePlayer(Player $player): Player;
}
