<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\GameModifier;

/**
 * @template-extends ArrayCollection<int, GameModifier>
 */
class ModifierCollection extends ArrayCollection
{
    public function addModifiers(self $modifierCollection): self
    {
        return new self(array_merge($this->toArray(), $modifierCollection->toArray()));
    }

    /**
     * `ModifierCollection::sortModifiers` sort modifiers by priority order.
     */
    public function sortModifiers(): self
    {
        $array = $this->toArray();

        usort($array, static function ($a, $b) {
            return ($a->getModifierConfig()->getPriority() < $b->getModifierConfig()->getPriority()) ? -1 : 1;
        });

        return new self($array);
    }

    public function getModifierFromConfig(AbstractModifierConfig $modifierConfig): ?GameModifier
    {
        $modifierConfig = $this->filter(static fn (GameModifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();

        return $modifierConfig ?: null;
    }

    public function getEventModifiers(AbstractGameEvent $event, array $priorities): self
    {
        return $this->filter(static fn (GameModifier $modifier) => (
            ($modifierConfig = $modifier->getModifierConfig()) instanceof EventModifierConfig
            && \in_array($modifierConfig->getPriority(), $priorities, true)
            && $modifierConfig->doModifierApplies($event)
            && (($charge = $modifier->getCharge()) === null || $charge->isCharged())
        ));
    }

    public function getDirectModifiers(): self
    {
        return $this->filter(
            static fn (GameModifier $modifier) => (
                $modifier->getModifierConfig()
            ) instanceof DirectModifierConfig
        );
    }

    public function getTargetModifiers(bool $condition): self
    {
        return $this->filter(static fn (GameModifier $modifier) => (
            ($modifierConfig = $modifier->getModifierConfig()) instanceof EventModifierConfig
            && $modifierConfig->getApplyOnTarget() === $condition
        ));
    }
}
