<?php

namespace Mush\RoomLog\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;

/**
 * @template-extends ArrayCollection<int, RoomLog>
 */
class RoomLogCollection extends ArrayCollection
{
    public function getLogsRelatedToPlayer(Player $player): self
    {
        $playerName = $player->getName();

        return $this->filter(static function (RoomLog $roomLog) use ($playerName) {
            $logParameters = $roomLog->getParameters();

            return (\array_key_exists('character', $logParameters) && ($logParameters['character'] === $playerName)) || (\array_key_exists('target_character', $logParameters) && ($logParameters['target_character'] === $playerName));
        });
    }

    public function getNumberOfUnreadLogsForPlayer(Player $player): int
    {
        return $this->filter(static function (RoomLog $roomLog) use ($player) {
            return $roomLog->isUnreadBy($player);
        })->count();
    }
}
