<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\ProbaCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Ration extends EquipmentMechanic
{
    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $moralPoints;
    // possibilities are stored as key, array value represent the probability to get the key value
    // see ProbaCollection Entity

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $actionPoints;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $movementPoints;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $healthPoints;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    protected ?int $satiety = null;

    // Store any extra effect the food has as key with the chance to get it as value
    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    private array $extraEffects;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Groups(['mechanic_read', 'mechanic_write'])]
    protected bool $isPerishable = true;

    public function __construct()
    {
        parent::__construct();
        $this->actionPoints = [0 => 1];
        $this->movementPoints = [0 => 1];
        $this->moralPoints = [0 => 1];
        $this->healthPoints = [0 => 1];
        $this->extraEffects = [];
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::RATION;

        return $mechanics;
    }

    public function getActionPoints(): ProbaCollection
    {
        return new ProbaCollection($this->actionPoints);
    }

    public function setActionPoints(array $actionPoints): static
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }

    public function getMovementPoints(): ProbaCollection
    {
        return new ProbaCollection($this->movementPoints);
    }

    public function setMovementPoints(array $movementPoints): static
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }

    public function getHealthPoints(): ProbaCollection
    {
        return new ProbaCollection($this->healthPoints);
    }

    public function setHealthPoints(array $healthPoints): static
    {
        $this->healthPoints = $healthPoints;

        return $this;
    }

    public function getMoralPoints(): ProbaCollection
    {
        return new ProbaCollection($this->moralPoints);
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

    public function getExtraEffects(): ProbaCollection
    {
        return new ProbaCollection($this->extraEffects);
    }

    public function setExtraEffects(array $extraEffects): static
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }

    public function getIsPerishable(): bool
    {
        return $this->isPerishable;
    }

    public function setIsPerishable(bool $isPerishable): static
    {
        $this->isPerishable = $isPerishable;

        return $this;
    }
}
