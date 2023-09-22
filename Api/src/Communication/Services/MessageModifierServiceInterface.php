<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Message;
use Mush\Player\Entity\Player;

interface MessageModifierServiceInterface
{
    public function applyModifierEffects(
        Message $message,
        ?Player $player,
        string $effectName
    ): Message;
}
