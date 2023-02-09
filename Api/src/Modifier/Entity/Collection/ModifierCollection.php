<?php

namespace Mush\Modifier\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
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

    public function getVariableEventModifiers(string $targetVariable): self
    {
        return $this->filter(function (GameModifier $modifier) use ($targetVariable) {
            $modifierConfig = $modifier->getModifierConfig();
            return ($modifierConfig instanceof  VariableEventModifierConfig &&
                $modifierConfig->getTargetVariable() === $targetVariable
            );
        });
    }

    public function getTriggerEventModifiers(): self
    {
        return $this->filter(fn (GameModifier $modifier) => ($modifier->getModifierConfig() instanceof TriggerEventModifierConfig));
    }

    public function getModifiersByHolderClass(string $reach): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getModifierHolderClass() === $reach);
    }

    public function getActionParameterModifiers(): self
    {
        return $this->filter(fn (GameModifier $modifier) => !$modifier->getModifierConfig()->getApplyOnParameterOnly());
    }

    public function getNoActionParameterModifiers(): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getApplyOnParameterOnly());
    }

    public function getModifiersByEvent(string $event): self
    {
        return $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig()->getTargetEvent() === $event);
    }

    public function getModifierFromConfig(AbstractModifierConfig $modifierConfig): ?GameModifier
    {
        $modifierConfig = $this->filter(fn (GameModifier $modifier) => $modifier->getModifierConfig() === $modifierConfig)->first();

        return $modifierConfig ?: null;
    }
}
