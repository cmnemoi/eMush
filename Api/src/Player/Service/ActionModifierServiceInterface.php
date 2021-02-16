<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;

interface ActionModifierServiceInterface
{
    public function getAdditiveModifier(Player $player, array $scopes, ?string $target = null): int;

    public function getMultiplicativeModifier(Player $player, array $scopes, ?string $target = null): float;
}
