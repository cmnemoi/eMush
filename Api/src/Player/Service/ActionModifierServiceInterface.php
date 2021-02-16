<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;

interface ActionModifierServiceInterface
{
    public function getModifiedValue(float $initValue, Player $player, array $scopes, ?string $target = null): int;
}
