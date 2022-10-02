<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;

interface PlayerVariableServiceInterface
{
    public function getMaxPlayerVariable(Player $player, string $variable): int;

    public function handleActionPointModifier(int $delta, Player $player): Player;

    public function handleMovementPointModifier(int $delta, Player $player): Player;

    public function handleHealthPointModifier(int $delta, Player $player): Player;

    public function handleMoralPointModifier(int $delta, Player $player): Player;

    public function handleSatietyModifier(int $delta, Player $player): Player;

    public function setPlayerVariableToMax(Player $player, string $target, \DateTime $date = null): Player;
}
