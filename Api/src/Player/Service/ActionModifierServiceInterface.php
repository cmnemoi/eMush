<?php

namespace Mush\Player\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Player\Entity\Player;

interface ActionModifierServiceInterface
{
    public function getActionModifier(Player $player, array $scopes, array $types, ?string $target = null): Collection;
}
