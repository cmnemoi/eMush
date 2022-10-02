<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_condition')]
class ModifierCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $condition = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $value = 100;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
