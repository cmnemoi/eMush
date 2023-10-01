<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\GameVariableCollection;

#[ORM\Entity]
class GameVariable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GameVariableCollection::class, inversedBy: 'gameVariable')]
    protected ?GameVariableCollection $gameVariableCollection;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    protected int $value;

    #[ORM\Column(type: 'integer', length: 255, nullable: true)]
    protected ?int $maxValue;

    #[ORM\Column(type: 'integer', length: 255, nullable: true)]
    protected ?int $minValue;

    public function __construct(
        ?GameVariableCollection $variableCollection,
        string $name,
        int $initValue,
        int $maxValue = null,
        ?int $minValue = 0
    ) {
        $this->name = $name;
        $this->gameVariableCollection = $variableCollection;
        $this->value = $initValue;
        $this->maxValue = $maxValue;
        $this->minValue = $minValue;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameVariableCollection(): ?GameVariableCollection
    {
        return $this->gameVariableCollection;
    }

    public function setGameVariableCollection(GameVariableCollection $gameVariableCollection): static
    {
        $this->gameVariableCollection = $gameVariableCollection;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        if ($this->maxValue) {
            $value = min($value, $this->maxValue);
        }
        if ($this->minValue) {
            $value = max($value, $this->minValue);
        }

        $this->value = $value;

        return $this;
    }

    public function getValueInRange(int $value): int
    {
        if ($this->maxValue !== null) {
            $value = min($value, $this->maxValue);
        }
        if ($this->minValue !== null) {
            $value = max($value, $this->minValue);
        }

        return $value;
    }

    public function changeValue(int $delta): static
    {
        $newValue = $this->value + $delta;
        $maxValue = $this->maxValue;
        $minValue = $this->minValue;

        if ($maxValue !== null && $maxValue < $newValue) {
            $this->value = $maxValue;
        } elseif ($minValue !== null && $minValue > $newValue) {
            $this->value = $minValue;
        } else {
            $this->value = $newValue;
        }

        return $this;
    }

    public function changeMaxValue(int $delta): static
    {
        if ($this->maxValue === null) {
            return $this;
        }

        $value = $this->value;
        $this->maxValue += $delta;

        if ($this->maxValue < $value) {
            $this->value = $this->maxValue;
        }

        return $this;
    }

    public function isMax(): bool
    {
        if ($this->maxValue === null) {
            return false;
        }

        return $this->maxValue === $this->value;
    }

    public function isMin(): bool
    {
        if ($this->minValue === null) {
            return false;
        }

        return $this->minValue === $this->value;
    }

    public function getMaxValue(): ?int
    {
        return $this->maxValue;
    }

    public function setMaxValue(?int $value): static
    {
        $this->maxValue = $value;

        return $this;
    }

    public function getMinValue(): ?int
    {
        return $this->minValue;
    }

    public function setMinValue(?int $value): static
    {
        $this->minValue = $value;

        return $this;
    }

    public function setValueByName(?int $value, string $name): static
    {
        switch ($name) {
            case 'value':
                if ($value !== null) {
                    $this->value = $value;
                }

                return $this;
            case 'max_value':
                $this->maxValue = $value;

                return $this;
            case 'min_value':
                $this->minValue = $value;

                return $this;
        }

        return $this;
    }

    public function setValuesFromArray(array $values): static
    {
        foreach ($values as $key => $value) {
            $this->setValueByName($value, $key);
        }

        return $this;
    }
}
