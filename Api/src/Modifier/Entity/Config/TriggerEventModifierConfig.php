<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity\Config;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Dto\TriggerEventModifierConfigDto;
use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * One of the modifier type
 * This type of modifier trigger an additional event when the target event is dispatched.
 *
 * visibility: the visibility of the triggered event
 * triggeredEventConfig: a config to create the triggered event
 * priority: priority of the new event (negative means before the initial event, 0 means replace the initial event)
 * targetFilters: filters to apply when selecting the target of the event. Currently 2 filters EXCLUDE_PROVIDER and SINGLE_RANDOM
 * eventTargetRequirements: allow to filter targets of the event according to various condition (name, hasStatus...)
 */
#[ApiResource(
    normalizationContext: ['groups' => ['modifier_config_read']],
    denormalizationContext: ['groups' => ['modifier_config_write']],
    paginationItemsPerPage: 25,
    security: 'is_granted("ROLE_USER")',
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_MODERATOR")',
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_MODERATOR")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
#[ORM\Entity]
class TriggerEventModifierConfig extends EventModifierConfig
{
    #[ORM\ManyToOne(targetEntity: AbstractEventConfig::class)]
    #[Groups(['modifier_config_read', 'modifier_config_write'])]
    protected AbstractEventConfig $triggeredEvent;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['modifier_config_read', 'modifier_config_write'])]
    protected string $visibility = VisibilityEnum::PUBLIC;

    #[ORM\ManyToMany(targetEntity: ModifierActivationRequirement::class)]
    protected Collection $eventTargetRequirements;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    #[Groups(['modifier_config_read', 'modifier_config_write'])]
    private array $targetFilters = [];

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->modifierActivationRequirements = new ArrayCollection([]);
        $this->modifierStrategy = ModifierStrategyEnum::ADD_EVENT;
        $this->addNoneTagName();
    }

    public static function fromDtoChild(TriggerEventModifierConfigDto $triggerEventModifierConfigDto, ?self $config = null): self
    {
        if ($config === null) {
            $config = new self($triggerEventModifierConfigDto->key);
        }

        $config->setModifierName($triggerEventModifierConfigDto->name)
            ->setModifierStrategy($triggerEventModifierConfigDto->strategy)
            ->setModifierRange($triggerEventModifierConfigDto->modifierRange);

        $config->setTargetEvent($triggerEventModifierConfigDto->targetEvent)
            ->setPriority($triggerEventModifierConfigDto->priority)
            ->setTagConstraints($triggerEventModifierConfigDto->tagConstraints)
            ->setApplyWhenTargeted($triggerEventModifierConfigDto->applyWhenTargeted);

        $config->setVisibility($triggerEventModifierConfigDto->visibility)
            ->setTargetFilters($triggerEventModifierConfigDto->targetFilters)
            ->setEventTargetRequirements($triggerEventModifierConfigDto->eventActivationRequirements);

        return $config;
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

    public function setModifierName(?string $modifierName): self
    {
        parent::setModifierName($modifierName);
        $this->addNoneTagName();

        return $this;
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

    public function getTriggeredVariableEventConfigOrThrow(): VariableEventConfig
    {
        return $this->triggeredEvent instanceof VariableEventConfig ? $this->triggeredEvent : throw new \RuntimeException("{$this->triggeredEvent->getName()} is not a variable event config!");
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

    public function getEventTargetRequirements(): ModifierActivationRequirementCollection
    {
        return new ModifierActivationRequirementCollection($this->eventTargetRequirements->toArray());
    }

    public function addEventActivationRequirement(ModifierActivationRequirement $requirement): self
    {
        $this->eventTargetRequirements->add($requirement);

        return $this;
    }

    public function setEventTargetRequirements(array|Collection $eventTargetRequirements): self
    {
        if (\is_array($eventTargetRequirements)) {
            $eventTargetRequirements = new ArrayCollection($eventTargetRequirements);
        }

        $this->eventTargetRequirements = $eventTargetRequirements;

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
}
