<?php

declare(strict_types=1);

namespace Mush\Communication\UseCase;

use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

final class GetContactablePlayersUseCase
{
    public function execute(Player $player): PlayerCollection
    {
        return $player->hasMeansOfCommunication() ?
            $player->getDaedalus()->getAlivePlayersWithMeansOfCommunication()->getAllExcept($player) :
            $player->getAlivePlayersInRoomExceptSelf();
    }
}
