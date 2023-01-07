<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Modifier\Enum\ModifierModeEnum;

/**
 * @ORM\Entity
 * @ORM\Table(name="modifier_config")
 */
#[ORM\Entity]
#[ORM\Table(name: 'modifier_config')]
class ModifierConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $modifierName = null;

    #[ORM\Column(type: 'float', nullable: false)]
    private float $delta = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetVariable;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $targetEvent;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $modifierHolderClass = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $mode = ModifierModeEnum::ADDITIVE;

    #[ORM\ManyToMany(targetEntity: ModifierActivationRequirement::class)]
    private Collection $modifierActivationRequirements;

    public function __construct()
    {
        $this->modifierActivationRequirements = new ArrayCollection([]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function buildName(): static
    {
        $modifierName = $this->modifierName;

        if ($modifierName === null) {
            $name = 'modifier';
        } else {
            $name = $modifierName;
        }

        $reach = $this->modifierHolderClass;
        if ($reach !== null) {
            $name = $name . '_for_' . $reach;
        }

        $mode = $this->mode;
        $delta = $this->delta;
        $target = $this->targetVariable;
        switch ($mode) {
            case ModifierModeEnum::ADDITIVE:
                if ($delta > 0) {
                    $name = $name . '_+' . strval($delta) . $target;
                } elseif ($delta < 0) {
                    $name = $name . '_-' . strval(-$delta) . $target;
                }
                break;
            case ModifierModeEnum::SET_VALUE:
                $name = $name . '_set_' . strval($delta) . $target;
                break;
            case ModifierModeEnum::MULTIPLICATIVE:
                $name = $name . '_x' . strval($delta) . $target;
                break;
        }

        $name = $name . '_on_' . $this->targetEvent;

        /** @var ModifierActivationRequirement $requirement */
        foreach ($this->modifierActivationRequirements as $requirement) {
            $name = $name . '_if_' . $requirement->getName();
        }

        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setModifierName(string $modifierName): self
    {
        $this->modifierName = $modifierName;

        return $this;
    }

    public function getModifierName(): ?string
    {
        return $this->modifierName;
    }

    public function getDelta(): float
    {
        return $this->delta;
    }

    public function setDelta(float $delta): self
    {
        $this->delta = $delta;

        return $this;
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

    public function getTargetVariable(): string
    {
        return $this->targetVariable;
    }

    public function setTargetVariable(string $targetVariable): self
    {
        $this->targetVariable = $targetVariable;

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

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

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
