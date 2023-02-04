<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\QuantityEventInterface;
use Mush\Modifier\Enum\ModifierModeEnum;

#[ORM\Entity]
class VariableEventModifierConfig extends ModifierConfig
{
    #[ORM\Column(type: 'float', nullable: false)]
    private float $delta = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetVariable;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $mode = ModifierModeEnum::ADDITIVE;

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
        $target = $this->targetVariable;
        switch ($mode) {
            case ModifierModeEnum::ADDITIVE:
                if ($delta > 0) {
                    $name = $name . '_+' . strval($delta) . $target;
                } elseif ($delta < 0) {
                    $name = $name . '_-' . strval(-$delta) . $target;
                }
                break;
            case ModifierModeEnum::SET_VALUE:
                $name = $name . '_set_' . strval($delta) . $target;
                break;
            case ModifierModeEnum::MULTIPLICATIVE:
                $name = $name . '_x' . strval($delta) . $target;
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
