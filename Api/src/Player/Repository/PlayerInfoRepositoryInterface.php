<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface PlayerInfoRepositoryInterface
{
    public function getCurrentPlayerInfoForUserOrNull(User $user): ?PlayerInfo;

    public function findOneByUserAndGameStatusOrNull(User $user, string $gameStatus): ?PlayerInfo;

    public function findPlayerInfoInSameGameOrNull(User $author, PlayerInfo $targetPlayerInfo): ?PlayerInfo;

    public function save(PlayerInfo $playerInfo): void;
}
