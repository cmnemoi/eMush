<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

final class InMemoryPlayerInfoRepository implements PlayerInfoRepositoryInterface
{
    /**
     * @var PlayerInfo[]
     */
    private array $playerInfos = [];

    public function getCurrentPlayerInfoForUserOrNull(User $user): ?PlayerInfo
    {
        foreach ($this->playerInfos as $playerInfo) {
            if ($playerInfo->getUser()->getUserId() === $user->getUserId()) {
                return $playerInfo;
            }
        }

        return null;
    }

    public function findOneByUserAndGameStatusOrNull(User $user, string $gameStatus): ?PlayerInfo
    {
        foreach ($this->playerInfos as $playerInfo) {
            if ($playerInfo->getUser()->getUserId() === $user->getUserId() && $playerInfo->getGameStatus() === $gameStatus) {
                return $playerInfo;
            }
        }

        return null;
    }

    public function save(PlayerInfo $playerInfo): void
    {
        $this->playerInfos[] = $playerInfo;
    }

    public function clear(): void
    {
        $this->playerInfos = [];
    }
}
