<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class GameVariable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GameVariableCollection::class, inversedBy: 'gameVariable')]
    protected GameVariableCollection $gameVariableCollection;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    protected int $value;

    #[ORM\Column(type: 'integer', length: 255, nullable: true)]
    protected ?int $maxValue;

    #[ORM\Column(type: 'integer', length: 255, nullable: true)]
    protected ?int $minValue;

    public function __construct(
        GameVariableCollection $variableCollection,
        string $name,
        int $initValue,
        ?int $maxValue = null,
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

    public function getGameVariableCollection(): GameVariableCollection
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
        $this->value = $value;

        return $this;
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
}
