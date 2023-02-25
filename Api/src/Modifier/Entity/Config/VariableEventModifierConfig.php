<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\VariableModifierModeEnum;

/**
 * One of the modifier type
 * This type of modifier change the output of an AbstractQuantityEvent
 * It can modify the applied modification but also the maximum and minimum values.
 *
 * delta: the amount of modification
 * targetVariable: the name of the variable that can be modified
 * mode: specify the mode of application of the delta (additive, multiplicative or set)
 * applyOn: specify if the modification is on the value or on the possible range of the value
 */
#[ORM\Entity]
class VariableEventModifierConfig extends EventModifierConfig
{
    #[ORM\Column(type: 'float', nullable: false)]
    private float $delta = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetVariable;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $mode = VariableModifierModeEnum::ADDITIVE;

    public function __construct()
    {
        $this->targetEvent = VariableEventInterface::CHANGE_VARIABLE;

        parent::__construct();
    }

    public function buildName(): static
    {
        $modifierName = $this->modifierName;

        if ($modifierName === null) {
            $name = 'modifier';
        } else {
            $name = $modifierName;
        }

        $name = $name . '_for_' . $this->modifierRange;

        $mode = $this->mode;
        $delta = $this->delta;
        $targetVariable = $this->targetVariable;
        switch ($mode) {
            case VariableModifierModeEnum::ADDITIVE:
                if ($delta > 0) {
                    $name = $name . '_+' . strval($delta) . $targetVariable;
                } elseif ($delta < 0) {
                    $name = $name . '_-' . strval(-$delta) . $targetVariable;
                }
                break;
            case VariableModifierModeEnum::SET_VALUE:
                $name = $name . '_set_' . strval($delta) . $targetVariable;
                break;
            case VariableModifierModeEnum::MULTIPLICATIVE:
                $name = $name . '_x' . strval($delta) . $targetVariable;
                break;
        }

        $name = $name . '_on_' . $this->targetEvent;

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $name = $name . '_if_' . $requirement->getName();
        }

        $this->name = $name;

        return $this;
    }

    public function getDelta(): float
    {
        return $this->delta;
    }

    public function setDelta(float $delta): self
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

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }
}
