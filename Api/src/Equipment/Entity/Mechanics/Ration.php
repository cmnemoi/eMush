<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Ration extends EquipmentMechanic
{
    #[ORM\Column(type: 'array', nullable: false)]
    private array $moralPoints = [0 => 1];
    //  possibilities are stored as key, array value represent the probability to get the key value

    #[ORM\Column(type: 'array', nullable: false)]
    private array $actionPoints = [0 => 1];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $movementPoints = [0 => 1];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $healthPoints = [0 => 1];

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $satiety = null;

    // Store any extra effect the food has as key with the chance to get it as value
    #[ORM\Column(type: 'array', nullable: false)]
    private array $extraEffects = [];

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $isPerishable = true;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::RATION;

        return $mechanics;
    }

    public function getActionPoints(): array
    {
        return $this->actionPoints;
    }

    public function setActionPoints(array $actionPoints): static
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }

    public function getMovementPoints(): array
    {
        return $this->movementPoints;
    }

    public function setMovementPoints(array $movementPoints): static
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }

    public function getHealthPoints(): array
    {
        return $this->healthPoints;
    }

    public function setHealthPoints(array $healthPoints): static
    {
        $this->healthPoints = $healthPoints;

        return $this;
    }

    public function getMoralPoints(): array
    {
        return $this->moralPoints;
    }

    public function setMoralPoints(array $moralPoints): static
    {
        $this->moralPoints = $moralPoints;

        return $this;
    }

    public function getSatiety(): ?int
    {
        return $this->satiety;
    }

    public function setSatiety(?int $satiety): static
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function getExtraEffects(): array
    {
        return $this->extraEffects;
    }

    public function setExtraEffects(array $extraEffects): static
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }

    public function isPerishable(): bool
    {
        return $this->isPerishable;
    }

    public function setIsPerishable(bool $isPerishable): static
    {
        $this->isPerishable = $isPerishable;

        return $this;
    }
}
