<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class ModifierCollection extends ArrayCollection
{
    public function addModifiers(self $modifierCollection): self
    {
        return new ModifierCollection(array_merge($this->toArray(), $modifierCollection->toArray()));
    }

    public function sortModifiersByDelta(bool $ascending = true): self
    {
        $modifiers = $this->toArray();
        usort($modifiers, function (Modifier $a, Modifier $b) use ($ascending) {
            $aDelta = $a->getConfig()->getDelta();
            $bDelta = $b->getConfig()->getDelta();
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
