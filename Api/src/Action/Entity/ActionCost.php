<?php

namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Player\Enum\PlayerVariableEnum;

#[ORM\Entity]
class ActionCost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $actionPointCost = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $movementPointCost = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $moralPointCost = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionPointCost(): int
    {
        return $this->actionPointCost;
    }

    public function setActionPointCost(int $actionPointCost): self
    {
        $this->actionPointCost = $actionPointCost;

        return $this;
    }

    public function getMovementPointCost(): int
    {
        return $this->movementPointCost;
    }

    public function setMovementPointCost(int $movementPointCost): self
    {
        $this->movementPointCost = $movementPointCost;

        return $this;
    }

    public function getMoralPointCost(): int
    {
        return $this->moralPointCost;
    }

    public function setMoralPointCost(?int $moralPointCost): self
    {
        $this->moralPointCost = $moralPointCost;

        return $this;
    }

    public function getVariableCost(string $variable): int
    {
        return match ($variable) {
            PlayerVariableEnum::ACTION_POINT => $this->actionPointCost,
            PlayerVariableEnum::MOVEMENT_POINT => $this->movementPointCost,
            PlayerVariableEnum::MORAL_POINT => $this->moralPointCost,
            default => 0,
        };
    }
}
