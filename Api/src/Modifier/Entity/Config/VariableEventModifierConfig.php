<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;

/**
 * One of the modifier type
 * This type of modifier change the output of an AbstractQuantityEvent
 * It can modify the applied modification but also the maximum and minimum values.
 *
 * delta: the amount of modification
 * targetVariable: the name of the variable that can be modified
 * mode: specify the mode of application of the delta (additive, multiplicative or set)
 *
 * By default, additive modifier priority : -120 / multiplicative modifier priority : -140
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

    public function __construct($name)
    {
        $this->targetEvent = VariableEventInterface::CHANGE_VARIABLE;
        $this->modifierStrategy = ModifierStrategyEnum::VARIABLE_MODIFIER;
        $this->priority = ModifierPriorityEnum::ADDITIVE_MODIFIER_VALUE;

        parent::__construct($name);
    }

    public static function fromConfigData(array $configData): self
    {
        $modifier = new self($configData['name']);

        $modifier
            ->setDelta($configData['delta'])
            ->setTargetVariable($configData['targetVariable'])
            ->setMode($configData['mode'])
            ->setPriority($configData['priority'])
            ->setTargetEvent($configData['targetEvent'])
            ->setApplyWhenTargeted($configData['applyOnTarget'])
            ->setTagConstraints($configData['tagConstraints'])
            ->setModifierRange($configData['modifierRange'])
            ->setModifierName($configData['modifierName']);

        return $modifier;
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
                    $name = $name . '_+' . $delta . $targetVariable;
                } elseif ($delta < 0) {
                    $name = $name . '_-' . -$delta . $targetVariable;
                }

                break;

            case VariableModifierModeEnum::SET_VALUE:
                $name = $name . '_set_' . $delta . $targetVariable;

                break;

            case VariableModifierModeEnum::MULTIPLICATIVE:
                $name = $name . '_x' . $delta . $targetVariable;

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

    public function setFloatDelta(string $delta): self
    {
        $this->delta = (float) $delta;

        return $this;
    }

    public function getFloatDelta(): string
    {
        return (string) $this->delta;
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

    public function doModifierApplies(AbstractGameEvent $event): bool
    {
        if (!$event instanceof VariableEventInterface || $event->getVariableName() !== $this->targetVariable) {
            return false;
        }

        return parent::doModifierApplies($event);
    }

    public function getTranslationKey(): ?string
    {
        if ($this->mode === VariableModifierModeEnum::SET_VALUE) {
            $key = $this->targetEvent . '_set_value';
        } elseif ($this->mode === VariableModifierModeEnum::MULTIPLICATIVE && $this->delta < 1
            || $this->mode === VariableModifierModeEnum::ADDITIVE && $this->delta < 0
        ) {
            $key = $this->targetEvent . '_decrease';
        } else {
            $key = $this->targetEvent . '_increase';
        }

        foreach (array_keys($this->tagConstraints) as $tagConstraint) {
            if ($this->tagConstraints[$tagConstraint] !== ModifierRequirementEnum::NONE_TAGS) {
                $key .= '_' . $tagConstraint;
            }
        }

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $key .= '_if_' . $requirement->getName();
        }

        return $key;
    }

    public function getTranslationParameters(): array
    {
        $parameters = parent::getTranslationParameters();

        $emoteMap = PlayerVariableEnum::getEmoteMap();
        if (isset($emoteMap[$this->targetVariable])) {
            $parameters['emote'] = $emoteMap[$this->targetVariable];
        }

        if ($this->mode === VariableModifierModeEnum::MULTIPLICATIVE) {
            $parameters['quantity'] = (1 - $this->delta) * 100;
        } else {
            $parameters['quantity'] = abs($this->delta);
        }

        return $parameters;
    }
}
