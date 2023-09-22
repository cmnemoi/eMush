<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;

/**
 * One of the modifier type
 * This type of modifier trigger an additional event when the target event is dispatched.
 *
 * visibility: the visibility of the triggered event
 * triggeredEventConfig: a config to create the triggered event
 * priority: priority of the new event (negative means before the initial event, 0 means replace the initial event)
 */
#[ORM\Entity]
class TriggerEventModifierConfig extends EventModifierConfig
{
    #[ORM\ManyToOne(targetEntity: AbstractEventConfig::class)]
    protected AbstractEventConfig $triggeredEvent;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $visibility = VisibilityEnum::PUBLIC;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->modifierStrategy = ModifierStrategyEnum::ADD_EVENT;
        $this->addNoneTagName();
    }

    public function buildName(string $configName): self
    {
        $baseName = $this->modifierName;
        $triggeredEvent = $this->triggeredEvent;

        if ($baseName === null) {
            $baseName = $triggeredEvent->getName();
        }

        $this->name = $baseName . '_ON_' . $this->getTargetEvent() . '_' . $configName;

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $this->name = $this->name . '_if_' . $requirement->getName();
        }

        $this->addNoneTagName();

        return $this;
    }

    public function setName(string $name): self
    {
        parent::setName($name);
        $this->addNoneTagName();

        return $this;
    }

    public function setModifierName(string|null $modifierName): self
    {
        parent::setModifierName($modifierName);
        $this->addNoneTagName();

        return $this;
    }

    // this prevents infinite loop where triggeredEvent can trigger itself
    private function addNoneTagName(): void
    {
        $modifierName = $this->modifierName;

        if ($modifierName === null) {
            $modifierName = $this->name;
            $this->modifierName = $modifierName;
        }

        $this->tagConstraints[$modifierName] = ModifierRequirementEnum::NONE_TAGS;
    }

    public function setTagConstraints(array $tagConstraints): self
    {
        parent::setTagConstraints($tagConstraints);

        $this->addNoneTagName();

        return $this;
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

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getTranslationKey(): ?string
    {
        return $this->triggeredEvent->getTranslationKey() . '_on_' . $this->targetEvent;
    }

    public function getTranslationParameters(): array
    {
        $parameters = parent::getTranslationParameters();

        return array_merge($parameters, $this->triggeredEvent->getTranslationParameters());
    }
}
