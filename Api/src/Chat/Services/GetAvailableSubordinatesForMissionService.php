<?php

declare(strict_types=1);

namespace Mush\Chat\Services;

use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

final readonly class GetAvailableSubordinatesForMissionService
{
    public function execute(Player $player): PlayerCollection
    {
        return $player->hasMeansOfCommunication()
            ? $player->getDaedalus()->getAlivePlayersWithMeansOfCommunication()->getAllExcept($player)
            : $player->getAlivePlayersInRoomExceptSelf();
    }
}
