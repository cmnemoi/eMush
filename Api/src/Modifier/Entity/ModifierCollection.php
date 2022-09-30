<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\Trash\ModifierConfig;

class ModifierCollection extends ArrayCollection
{
    public function addModifiers(self $modifierCollection): self
    {
        return new ModifierCollection(array_merge($this->toArray(), $modifierCollection->toArray()));
    }

    public function getTargetedModifiers(string $target): self
    {
        return $this->filter(fn (Modifier $modifier) => $modifier->getModifierConfig()->getTarget() === $target);
    }

    public function getReachedModifiers(string $reach): self
    {
        return $this->filter(fn (Modifier $modifier) => $modifier->getModifierConfig()->getReach() === $reach);
    }

    public function getScopedModifiers(array $scopes): self
    {
        return $this->filter(fn (Modifier $modifier) => in_array($modifier->getModifierConfig()->getScope(), $scopes));
    }

    public function getModifierFromConfig(ModifierConfig $modifierConfig): Modifier
    {
        return $this->filter(fn (Modifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();
    }

    public function sortModifiersByDelta(bool $ascending = true): self
    {
        $modifiers = $this->toArray();
        usort($modifiers, function (Modifier $a, Modifier $b) use ($ascending) {
            $aDelta = $a->getModifierConfig()->getDelta();
            $bDelta = $b->getModifierConfig()->getDelta();
            if ($aDelta === $bDelta) {
                return 0;
            }

            if ($ascending) {
                return $aDelta > $bDelta ? 1 : -1;
            }

            return $aDelta < $bDelta ? 1 : -1;
        });

        return new ModifierCollection($modifiers);
    }
}
