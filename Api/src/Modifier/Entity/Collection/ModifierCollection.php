<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierConfig;

/**
 * @template-extends ArrayCollection<int, GameModifier>
 */
class ModifierCollection extends ArrayCollection
{
    public function addModifiers(self $modifierCollection): self
    {
        return new ModifierCollection(array_merge($this->toArray(), $modifierCollection->toArray()));
    }

    public function getTargetedModifiers(string $target): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getTargetVariable() === $target);
    }

    public function getReachedModifiers(string $reach): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getModifierHolderClass() === $reach);
    }

    public function getScopedModifiers(array $scopes): self
    {
        return $this->filter(fn (GameModifier $modifier) => in_array($modifier->getModifierConfig()->getTargetEvent(), $scopes));
    }

    public function getModifierFromConfig(ModifierConfig $modifierConfig): ?GameModifier
    {
        $modifierConfig = $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();

        return $modifierConfig ?: null;
    }

    public function sortModifiersByDelta(bool $ascending = true): self
    {
        $modifiers = $this->toArray();
        usort($modifiers, function (GameModifier $a, GameModifier $b) use ($ascending) {
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
