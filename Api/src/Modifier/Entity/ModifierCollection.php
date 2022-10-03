<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Config\ModifierConfig;

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
