<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
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

    public function getModifierFromConfig(AbstractModifierConfig $modifierConfig): ?GameModifier
    {
        $modifierConfig = $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();

        return $modifierConfig ?: null;
    }

    public function getEventModifiers(AbstractGameEvent $event): self
    {
        return $this->filter(fn (GameModifier $modifier) => (
            $modifier->getModifierConfig()->doModifierApplies($event)
            && (($charge = $modifier->getCharge()) === null || $charge->getCharge() > 0)
        ));
    }

    public function getTargetModifiers(bool $condition): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getApplyOnTarget() === $condition);
    }

    public function getTriggerEventModifiersNoReplace(): self
    {
        return $this->filter(fn (GameModifier $modifier) => ($modifierConfig = $modifier->getModifierConfig()) instanceof TriggerEventModifierConfig
            && !$modifierConfig->getReplaceEvent()
        );
    }

    public function getTriggerEventModifiersReplace(): self
    {
        return $this->filter(fn (GameModifier $modifier) => ($modifierConfig = $modifier->getModifierConfig()) instanceof TriggerEventModifierConfig
            && $modifierConfig->getReplaceEvent()
        );
    }

    public function getVariableEventModifiers(): self
    {
        return $this->filter(fn (GameModifier $modifier) => ($modifier->getModifierConfig()) instanceof VariableEventModifierConfig);
    }
}
