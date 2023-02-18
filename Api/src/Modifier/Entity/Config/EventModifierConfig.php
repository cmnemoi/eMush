<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class storing the various information needed to create and apply an eventModifier.
 * EventModifiers allows the creation of a game modifier that is activated whenever the target event is dispatched.
 *
 * targetEvent: the name of the event that trigger this modifier (apply modifier)
 * applyOnActionParameter: specify if the modifier only is applied when the holder is the target of an action (apply modifier)
 */
#[ORM\Entity]
class EventModifierConfig extends AbstractModifierConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    protected string $targetEvent;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $applyOnActionParameter = false;

    public function getTargetEvent(): string
    {
        return $this->targetEvent;
    }

    public function setTargetEvent(string $targetEvent): self
    {
        $this->targetEvent = $targetEvent;

        return $this;
    }

    public function getApplyOnParameterOnly(): bool
    {
        return $this->applyOnActionParameter;
    }

    public function setApplyOnParameterOnly(bool $onTargetOnly): self
    {
        $this->applyOnActionParameter = $onTargetOnly;

        return $this;
    }
}
