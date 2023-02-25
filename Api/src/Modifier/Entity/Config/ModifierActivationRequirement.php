<?php

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_activation_requirement')]
class ModifierActivationRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $activationRequirementName;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $activationRequirement = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $value = 100;

    public function __construct(string $activationRequirementName)
    {
        $this->activationRequirementName = $activationRequirementName;
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
        $name = $this->activationRequirementName;

        if ($this->activationRequirement !== null) {
            $name = $name . '_' . $this->activationRequirement;
        }
        if ($this->value !== 100) {
            $name = $name . '_' . $this->value;
        }
        $this->name = $name;

        return $this;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setActivationRequirement(string|null $activationRequirement): self
    {
        $this->activationRequirement = $activationRequirement;

        return $this;
    }

    public function getActivationRequirementName(): string
    {
        return $this->activationRequirementName;
    }

    public function getActivationRequirement(): ?string
    {
        return $this->activationRequirement;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
