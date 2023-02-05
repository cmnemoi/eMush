<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;

/**
 * One of the modifier type
 * This type of modifier trigger an additional event when the target event is dispatched
 * The triggered event is a QuantityEvent.
 *
 * targetVariable: the variable that is modified
 * quantity: the amount of point that is added or removed
 */
#[ORM\Entity]
class TriggerVariableEventModifierConfig extends TriggerEventModifierConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetVariable;

    #[ORM\Column(type: 'float', nullable: false)]
    private int $delta = 0;

    public function buildName(string $configName): self
    {
        $quantity = $this->delta;
        $modifiedVariable = $this->targetVariable;

        $baseName = strval($quantity) . $modifiedVariable;

        $this->name = $baseName . '_ON_' . $this->getTargetEvent() . '_' . $configName;

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $this->name = $this->name . '_if_' . $requirement->getName();
        }

        return $this;
    }

    public function getDelta(): int
    {
        return intval($this->delta);
    }

    public function setDelta(int $delta): self
    {
        $this->delta = $delta;

        return $this;
    }

    public function getTargetVariable(): string
    {
        return $this->targetVariable;
    }

    public function setTargetVariable(string $targetVariable): self
    {
        $this->targetVariable = $targetVariable;

        return $this;
    }
}
