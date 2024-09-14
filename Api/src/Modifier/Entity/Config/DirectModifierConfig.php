<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Enum\ModifierStrategyEnum;

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

    #[ORM\ManyToMany(targetEntity: ModifierActivationRequirement::class)]
    protected Collection $eventActivationRequirements;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $targetFilters = [];

    public function __construct($name)
    {
        $this->modifierStrategy = ModifierStrategyEnum::DIRECT_MODIFIER;
        $this->eventActivationRequirements = new ArrayCollection([]);

        parent::__construct($name);
    }

    public static function fromConfigData(array $configData): self
    {
        $directModifierConfig = new self($configData['name']);
        $directModifierConfig
            ->setRevertOnRemove($configData['revertOnRemove'])
            ->setModifierActivationRequirements($configData['modifierActivationRequirements'])
            ->setModifierRange($configData['modifierRange'])
            ->setModifierStrategy($configData['strategy']);

        return $directModifierConfig;
    }

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

    public function getEventActivationRequirements(): ModifierActivationRequirementCollection
    {
        return new ModifierActivationRequirementCollection($this->eventActivationRequirements->toArray());
    }

    public function addEventActivationRequirement(ModifierActivationRequirement $requirement): self
    {
        $this->eventActivationRequirements->add($requirement);

        return $this;
    }

    public function setEventActivationRequirements(array|Collection $eventActivationRequirements): self
    {
        if (\is_array($eventActivationRequirements)) {
            $eventActivationRequirements = new ArrayCollection($eventActivationRequirements);
        }

        $this->eventActivationRequirements = $eventActivationRequirements;

        return $this;
    }

    public function setTargetFilters(array $targetFilters): self
    {
        $this->targetFilters = $targetFilters;

        return $this;
    }

    public function getTargetFilters(): array
    {
        return $this->targetFilters;
    }
}
