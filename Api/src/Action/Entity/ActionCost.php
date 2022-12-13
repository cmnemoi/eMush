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

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $actionPointCost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $movementPointCost = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $moralPointCost = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionPointCost(): ?int
    {
        return $this->actionPointCost;
    }

    public function setActionPointCost(?int $actionPointCost): self
    {
        $this->actionPointCost = $actionPointCost;

        return $this;
    }

    public function getMovementPointCost(): ?int
    {
        return $this->movementPointCost;
    }

    public function setMovementPointCost(?int $movementPointCost): self
    {
        $this->movementPointCost = $movementPointCost;

        return $this;
    }

    public function getMoralPointCost(): ?int
    {
        return $this->moralPointCost;
    }

    public function setMoralPointCost(?int $moralPointCost): self
    {
        $this->moralPointCost = $moralPointCost;

        return $this;
    }

    public function getVariableCost(string $variable): ?int
    {
        switch ($variable) {
            case PlayerVariableEnum::ACTION_POINT:
                return $this->actionPointCost;
            case PlayerVariableEnum::MOVEMENT_POINT:
                return $this->movementPointCost;
            case PlayerVariableEnum::MORAL_POINT:
                return $this->moralPointCost;
        }

        return null;
    }

    public function buildName(): static
    {
        $name = 'cost';

        $actionPointCost = $this->actionPointCost;
        $movementPointCost = $this->movementPointCost;
        $moralPointCost = $this->moralPointCost;

        if ($actionPointCost !== 0) {
            $string = strval($actionPointCost);
            $name = $name . '_' . $string . '_action';
        }
        if ($movementPointCost !== 0) {
            $string = strval($movementPointCost);
            $name = $name . '_' . $string . '_movement';
        }
        if ($moralPointCost !== 0) {
            $string = strval($moralPointCost);
            $name = $name . '_' . $string . '_morale';
        }

        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
