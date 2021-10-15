<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;

class ModifierCollection extends ArrayCollection
{
    public function addModifiers(ModifierCollection $modifierCollection): ModifierCollection
    {
        return new ModifierCollection(array_merge($this->toArray(), $modifierCollection->toArray()));
    }

    public function getTargetedModifiers(string $target): ModifierCollection
    {
        return $this->filter(fn (Modifier $modifier) => $modifier->getModifierConfig()->getTarget() === $target);
    }

    public function getScopedModifiers(array $scopes): ModifierCollection
    {
        return $this->filter(fn (Modifier $modifier) => in_array($modifier->getModifierConfig()->getScope(), $scopes));
    }

    public function getModifierFromConfig(ModifierConfig $modifierConfig): Modifier
    {
        return $this->filter(fn (Modifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();
    }
}
