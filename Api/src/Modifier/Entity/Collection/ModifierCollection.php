<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;

class ModifierCollection extends ArrayCollection
{
    public function addModifiers(self $modifierCollection): self
    {
        return new ModifierCollection(array_merge($this->toArray(), $modifierCollection->toArray()));
    }

    public function getModifierFromConfig(ModifierConfig $modifierConfig): ?Modifier
    {
        $modifier = $this->filter(fn (Modifier $modifier) => $modifier->getConfig() === $modifierConfig)->first();

        return !$modifier ? null : $modifier;
    }
}
