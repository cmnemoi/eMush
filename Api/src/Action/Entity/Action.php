<?php

namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

#[ORM\Entity]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $types = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $scope;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $visibilities = [
        ActionOutputEnum::SUCCESS => VisibilityEnum::PUBLIC,
        ActionOutputEnum::FAIL => VisibilityEnum::PRIVATE,
    ];

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $successRate = 100;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $injuryRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dirtyRate = 0;

    #[ORM\ManyToOne(targetEntity: ActionCost::class)]
    private ActionCost $actionCost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTypes(): array
    {
        $types = $this->types;

        if (in_array($this->visibilities[ActionOutputEnum::SUCCESS], [VisibilityEnum::SECRET, VisibilityEnum::COVERT])) {
            $types[] = $this->visibilities[ActionOutputEnum::SUCCESS];
        }

        return $types;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

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

    public function getSuccessRate(): int
    {
        return $this->successRate;
    }

    public function setSuccessRate(int $successRate): self
    {
        $this->successRate = $successRate;

        return $this;
    }

    public function getInjuryRate(): int
    {
        return $this->injuryRate;
    }

    public function setInjuryRate(int $injuryRate): self
    {
        $this->injuryRate = $injuryRate;

        return $this;
    }

    public function getDirtyRate(): int
    {
        return $this->dirtyRate;
    }

    public function setDirtyRate(int $dirtyRate): self
    {
        $this->dirtyRate = $dirtyRate;

        return $this;
    }

    public function getActionCost(): ActionCost
    {
        return $this->actionCost;
    }

    public function setActionCost(ActionCost $actionCost): self
    {
        $this->actionCost = $actionCost;

        return $this;
    }

    public function getVisibility(string $actionOutput): string
    {
        if (key_exists($actionOutput, $this->visibilities)) {
            return $this->visibilities[$actionOutput];
        }

        return VisibilityEnum::HIDDEN;
    }

    public function setVisibility(string $actionOutput, string $visibility): self
    {
        $this->visibilities[$actionOutput] = $visibility;

        return $this;
    }
}
