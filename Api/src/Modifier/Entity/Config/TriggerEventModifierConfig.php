<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\VisibilityEnum;

#[ORM\Entity]
class TriggerEventModifierConfig extends AbstractModifierConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $triggeredEvent;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $visibility = VisibilityEnum::PUBLIC;

    #[ORM\Column(type: 'string')]
    private ?string $modifiedVariable = null;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 0;

    public function getModifierName(): ?string
    {
        return $this->modifierName;
    }

    public function buildName(string $configName): self
    {
        $quantity = $this->quantity;
        $modifiedVariable = $this->modifiedVariable;
        $baseName = $this->modifierName;

        if ($modifiedVariable !== null) {
            $baseName = strval($quantity) . $modifiedVariable;
        } elseif ($baseName === null) {
            $baseName = $this->triggeredEvent;
        }

        $this->name = $baseName . '_ON_' . $this->getTargetEvent() . '_' . $configName;

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $this->name = $this->name . '_if_' . $requirement->getName();
        }

        return $this;
    }

    public function getTriggeredEvent(): string
    {
        return $this->triggeredEvent;
    }

    public function setTriggeredEvent(string $triggeredEvent): self
    {
        $this->triggeredEvent = $triggeredEvent;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getModifiedVariable(): ?string
    {
        return $this->modifiedVariable;
    }

    public function setModifiedVariable(string $modifiedVariable): self
    {
        $this->modifiedVariable = $modifiedVariable;

        return $this;
    }
}
