<?php

namespace Mush\Modifier\Entity;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

trait ModifierProviderTrait
{
    public function getModifierHolderFromConfig(string $modifierHolderClass): ?ModifierHolderInterface
    {
        return match ($modifierHolderClass) {
            ModifierHolderClassEnum::DAEDALUS => $this->getDaedalus(),
            ModifierHolderClassEnum::PLACE => $this->getPlace(),
            ModifierHolderClassEnum::PLAYER => $this->getPlayer(),
            ModifierHolderClassEnum::EQUIPMENT => $this->getGameEquipment(),
            default => null,
        };
    }

    // unless specified in the class using the trait return null for those function
    public function getPlayer(): ?Player
    {
        return null;
    }

    public function getPlace(): ?Place
    {
        return null;
    }

    public function getGameEquipment(): ?GameEquipment
    {
        return null;
    }
}
