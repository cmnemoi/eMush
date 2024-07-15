<?php

declare(strict_types=1);

namespace Mush\Player\UseCase;

use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;

final class GetUserCurrentPlayerUseCase
{
    public function __construct(private PlayerInfoRepositoryInterface $playerInfoRepository) {}

    public function execute(User $user): Player
    {
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);
        if (($player = $playerInfo?->getPlayer()) === null) {
            throw new \RuntimeException("User {$user->getUsername()} is not ingame.");
        }

        return $player;
    }
}
