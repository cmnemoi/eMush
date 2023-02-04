<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\VariableEventModifierConfig;

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
        return $this->filter(fn (GameModifier $modifier) => (
            ($modifierConfig = $modifier->getModifierConfig()) instanceof VariableEventModifierConfig &&
            $modifierConfig->getTargetVariable() === $target));
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
}
