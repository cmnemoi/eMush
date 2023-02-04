<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Modifier\Enum\VariableModifierModeEnum;

#[ORM\Entity]
class VariableEventModifierConfig extends AbstractModifierConfig
{
    #[ORM\Column(type: 'float', nullable: false)]
    private float $delta = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetVariable;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $mode = VariableModifierModeEnum::ADDITIVE;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $appliesOn = VariableModifierModeEnum::VALUE;

    public function __construct()
    {
        $this->targetEvent = QuantityEventInterface::CHANGE_VARIABLE;

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

        $reach = $this->modifierHolderClass;
        if ($reach !== null) {
            $name = $name . '_for_' . $reach;
        }

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

    public function getAppliesOn(): string
    {
        return $this->appliesOn;
    }

    public function setAppliesOn(string $appliesOn): self
    {
        $this->appliesOn = $appliesOn;

        return $this;
    }
}
