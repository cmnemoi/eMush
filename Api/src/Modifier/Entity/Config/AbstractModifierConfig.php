<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class storing the various information needed to create and apply a modifier.
 *
 * name: a unique name needed for the DB
 * modifierName: the name of the modifier is used to create log associated with a modifier (apply modifier)
 * targetEvent: the name of the event that trigger this modifier (apply modifier)
 * applyOnActionParameter: specify if the modifier only is applied when the holder is the target of an action (apply modifier)
 * modifierHolderClass: the class that will hold the GameModifier entity (create modifier)
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'trigger_event_modifier_config' => TriggerEventModifierConfig::class,
    'variable_event_modifier_config' => VariableEventModifierConfig::class,
    'trigger_variable_event_modifier_config' => TriggerVariableEventModifierConfig::class,
])]
abstract class AbstractModifierConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $modifierName = null;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $targetEvent;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $applyOnActionParameter = false;

    #[ORM\Column(type: 'string', nullable: false)]
    protected ?string $modifierHolderClass = null;

    #[ORM\ManyToMany(targetEntity: ModifierActivationRequirement::class)]
    protected Collection $modifierActivationRequirements;

    public function __construct()
    {
        $this->modifierActivationRequirements = new ArrayCollection([]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setModifierName(string|null $modifierName): self
    {
        $this->modifierName = $modifierName;

        return $this;
    }

    public function getModifierName(): ?string
    {
        return $this->modifierName;
    }

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

    public function getModifierHolderClass(): ?string
    {
        return $this->modifierHolderClass;
    }

    public function setModifierHolderClass(string $modifierHolderClass): self
    {
        $this->modifierHolderClass = $modifierHolderClass;

        return $this;
    }

    public function getModifierActivationRequirements(): Collection
    {
        return $this->modifierActivationRequirements;
    }

    public function addModifierRequirement(ModifierActivationRequirement $modifierRequirement): self
    {
        $this->modifierActivationRequirements->add($modifierRequirement);

        return $this;
    }

    public function setModifierActivationRequirements(array|Collection $modifierActivationRequirements): self
    {
        if (is_array($modifierActivationRequirements)) {
            $modifierActivationRequirements = new ArrayCollection($modifierActivationRequirements);
        }

        $this->modifierActivationRequirements = $modifierActivationRequirements;

        return $this;
    }
}
