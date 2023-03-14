<?php

namespace Mush\Player\Event;

use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerInfectedEvent extends PlayerVariableEvent implements LoggableEventInterface, VariableEventInterface
{
    public function __construct(
        Player $player,
        string $variableName,
        int $quantity,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($player, $variableName, $quantity, $tags, $time);
    }
}
