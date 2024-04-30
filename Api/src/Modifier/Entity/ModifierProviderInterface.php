<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

interface ModifierProviderInterface
{
    public function getModifierHolderFromConfig(string $modifierHolderClass): ?ModifierHolderInterface;

    public function getModifierConfigs(): Collection;

    public function getDaedalus(): Daedalus;

    public function getPlace(): ?Place;

    public function getPlayer(): ?Player;

    public function getGameEquipment(): ?GameEquipment;
}
