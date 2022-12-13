<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'symptom_condition')]
class SymptomCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $conditionName;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $condition = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $value = 100;

    public function __construct(string $conditionName)
    {
        $this->conditionName = $conditionName;
    }

    public function getId(): ?int
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

    public function buildName(): static
    {
        $name = $this->conditionName;
        if ($this->condition !== null) {
            $name = $name . '_' . $this->condition;
        }
        if ($this->value !== 100) {
            $name = $name . '_' . $this->value;
        }
        $this->name = $name;

        return $this;
    }

    public function setConditionName(string $conditionName): self
    {
        $this->conditionName = $conditionName;

        return $this;
    }

    public function getConditionName(): string
    {
        return $this->conditionName;
    }

    public function setCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
