<?php

namespace Mush\Chat\Services;

use Mush\Chat\Entity\Message;
use Mush\Player\Entity\Player;

interface MessageModifierServiceInterface
{
    public function applyModifierEffects(
        Message $message,
        ?Player $player,
        string $effectName
    ): Message;
}
