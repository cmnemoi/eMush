<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class storing the various information needed to create and apply Modifiers.
 *
 * name: a unique name needed for the DB
 * modifierName: the name of the modifier is used to create log associated with a modifier (apply modifier)
 * modifierRange: the class that will hold the GameModifier entity (create modifier) (player, daedalus, place or gameEquipment)
 * modifierActivationRequirements: requirements that need to be fulfilled for the modifier to activate
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'event_modifier_config' => EventModifierConfig::class,
    'trigger_event_modifier_config' => TriggerEventModifierConfig::class,
    'variable_event_modifier_config' => VariableEventModifierConfig::class,
    'direct_modifier_config' => DirectModifierConfig::class,
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
    protected string $modifierStrategy;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $modifierRange;

    #[ORM\ManyToMany(targetEntity: ModifierActivationRequirement::class)]
    protected Collection $modifierActivationRequirements;

    public function __construct(string $name)
    {
        $this->modifierActivationRequirements = new ArrayCollection([]);
        $this->name = $name;
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

    public function getModifierStrategy(): string
    {
        return $this->modifierStrategy;
    }

    public function setModifierStrategy(string $modifierStrategy): self
    {
        $this->modifierStrategy = $modifierStrategy;

        return $this;
    }

    public function getModifierRange(): string
    {
        return $this->modifierRange;
    }

    public function setModifierRange(string $modifierRange): self
    {
        $this->modifierRange = $modifierRange;

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

    public function getTranslationKey(): ?string
    {
        return $this->modifierName;
    }

    public function getTranslationParameters(): array
    {
        $parameters = [];

        foreach ($this->modifierActivationRequirements as $requirement) {
            $parameters = array_merge($parameters, $requirement->getTranslationParameters());
        }

        if (!key_exists('chance', $parameters)) {
            $parameters['chance'] = 100;
        }

        return $parameters;
    }
}
