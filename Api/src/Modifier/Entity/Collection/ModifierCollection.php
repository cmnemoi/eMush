<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;

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
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getModifierRange() === $reach);
    }

    public function getScopedModifiers(array $scopes): self
    {
        return $this->filter(fn (GameModifier $modifier) => (
            $modifierConfig = $modifier->getModifierConfig()) instanceof EventModifierConfig &&
            in_array($modifierConfig->getTargetEvent(), $scopes));
    }

    public function getModifierFromConfig(AbstractModifierConfig $modifierConfig): ?GameModifier
    {
        $modifierConfig = $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();

        return $modifierConfig ?: null;
    }

    public function getEventModifiers(AbstractGameEvent $event): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->doModifierApplies($event));
    }

    public function getTargetModifiers(bool $condition): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getApplyOnTarget() === $condition);
    }
}
