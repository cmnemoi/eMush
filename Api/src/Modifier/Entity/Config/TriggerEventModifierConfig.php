<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\VisibilityEnum;

/**
 * One of the modifier type
 * This type of modifier trigger an additional event when the target event is dispatched.
 *
 * visibility: the visibility of the triggered event
 * triggeredEvent: the name of the triggered event
 */
#[ORM\Entity]
class TriggerEventModifierConfig extends AbstractModifierConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    protected string $triggeredEvent;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $visibility = VisibilityEnum::PUBLIC;

    public function buildName(string $configName): self
    {
        $baseName = $this->modifierName;

        if ($baseName === null) {
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
}
