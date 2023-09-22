<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;

/**
 * Class storing the various information needed to apply a directModifier.
 * Whenever a directModifier is applied (e.g. new disease, picking a skill...) or removed,
 * the effect of the directModifier is dispatched.
 *
 * eventConfig: a config to create an event
 * revertOnRemove: is the contrary effect dispatched when the modifier is removed
 */
#[ORM\Entity]
class DirectModifierConfig extends AbstractModifierConfig
{
    #[ORM\ManyToOne(targetEntity: AbstractEventConfig::class)]
    protected AbstractEventConfig $triggeredEvent;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $revertOnRemove = false;

    public function getTriggeredEvent(): AbstractEventConfig
    {
        return $this->triggeredEvent;
    }

    public function setTriggeredEvent(AbstractEventConfig $triggeredEvent): self
    {
        $this->triggeredEvent = $triggeredEvent;

        return $this;
    }

    public function getRevertOnRemove(): bool
    {
        return $this->revertOnRemove;
    }

    public function setRevertOnRemove(bool $revertOnRemove): self
    {
        $this->revertOnRemove = $revertOnRemove;

        return $this;
    }

    public function getTranslationKey(): ?string
    {
        return $this->triggeredEvent->getTranslationKey();
    }

    public function getTranslationParameters(): array
    {
        return $this->triggeredEvent->getTranslationParameters();
    }
}
