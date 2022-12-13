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
    private string $target;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $scope;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $reach = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $mode = ModifierModeEnum::ADDITIVE;

    #[ORM\ManyToMany(targetEntity: ModifierCondition::class)]
    private Collection $modifierConditions;

    public function __construct()
    {
        $this->modifierConditions = new ArrayCollection([]);
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

        $reach = $this->reach;
        if ($reach !== null) {
            $name = $name . '_for_' . $reach;
        }

        $mode = $this->mode;
        $delta = $this->delta;
        $target = $this->target;
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

        $name = $name . '_on_' . $this->scope;

        /** @var ModifierCondition $condition */
        foreach ($this->modifierConditions as $condition) {
            $name = $name . '_if_' . $condition->getName();
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

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getReach(): ?string
    {
        return $this->reach;
    }

    public function setReach(string $reach): self
    {
        $this->reach = $reach;

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

    public function getModifierConditions(): Collection
    {
        return $this->modifierConditions;
    }

    public function addModifierCondition(ModifierCondition $modifierCondition): self
    {
        $this->modifierConditions->add($modifierCondition);

        return $this;
    }

    public function setModifierConditions(array|Collection $modifierConditions): self
    {
        if (is_array($modifierConditions)) {
            $modifierConditions = new ArrayCollection($modifierConditions);
        }

        $this->modifierConditions = $modifierConditions;

        return $this;
    }
}
