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
use Mush\Modifier\Dto\DirectModifierConfigDto;
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
#[ApiResource(
    paginationItemsPerPage: 25,
    security: 'is_granted("ROLE_USER")',
    normalizationContext: ['groups' => ['modifier_config_read']],
    denormalizationContext: ['groups' => ['modifier_config_write']],
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

    public static function fromDto(DirectModifierConfigDto $directModifierConfigDto, ?self $config = null): self
    {
        $config ?? $config = new self($directModifierConfigDto->key);

        $config->setModifierName($directModifierConfigDto->name)
            ->setModifierStrategy($directModifierConfigDto->strategy)
            ->setModifierRange($directModifierConfigDto->modifierRange);

        $config->setRevertOnRemove($directModifierConfigDto->revertOnRemove)
            ->setTargetFilters($directModifierConfigDto->targetFilters);

        return $config;
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
