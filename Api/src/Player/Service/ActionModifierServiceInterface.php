<?php

namespace Mush\Player\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Player\Entity\Player;

interface ActionModifierServiceInterface
{
    public function getAdditiveModifier(Player $player, array $scopes, array $types, ?string $target = null): int;

    public function getMultiplicativeModifier(Player $player, array $scopes, array $types, ?string $target = null): int;
}
