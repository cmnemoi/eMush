<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;

interface PlayerVariableServiceInterface
{
    public function handleGameVariableChange(string $variableName, int $delta, Player $player): Player;

    public function setPlayerVariableToMax(Player $player, string $variableName, ?\DateTime $date = null): Player;
}
