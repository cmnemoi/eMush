<?php

namespace Mush\Modifier\Entity;

use Mush\Modifier\Entity\Collection\ModifierCollection;

trait ModifierHolderTrait
{
    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->map(static fn (ModifierHolder $modifier) => $modifier->getGameModifier())->toArray());
    }

    public function addModifier(GameModifier $modifier): static
    {
        $this->modifiers->add($modifier->getModifierHolderJoinTable());

        return $this;
    }

    public function removeModifier(GameModifier $modifier): static
    {
        $this->modifiers->removeElement($modifier->getModifierHolderJoinTable());

        return $this;
    }

    public function hasModifierByModifierName(string $modifierName): bool
    {
        return $this->modifiers->exists(static fn ($_, ModifierHolder $modifier) => $modifier->getGameModifier()->getModifierNameOrNull() === $modifierName);
    }
}
