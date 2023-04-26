<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\ProbaCollection;

#[ORM\Entity]
class Ration extends EquipmentMechanic
{
    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $moralPoints;
    //  possibilities are stored as key, array value represent the probability to get the key value

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $actionPoints;

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $movementPoints;

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $healthPoints;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $satiety = null;

    // Store any extra effect the food has as key with the chance to get it as value
    #[ORM\Column(type: 'array', nullable: false)]
    private ProbaCollection $extraEffects;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $isPerishable = true;

    public function __construct()
    {
        parent::__construct();
        $this->actionPoints = new ProbaCollection([0 => 1]);
        $this->movementPoints = new ProbaCollection([0 => 1]);
        $this->moralPoints = new ProbaCollection([0 => 1]);
        $this->healthPoints = new ProbaCollection([0 => 1]);
        $this->extraEffects = new ProbaCollection();
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::RATION;

        return $mechanics;
    }

    public function getActionPoints(): ProbaCollection
    {
        return $this->actionPoints;
    }

    public function setActionPoints(array $actionPoints): static
    {
        $this->actionPoints = new ProbaCollection($actionPoints);

        return $this;
    }

    public function getMovementPoints(): ProbaCollection
    {
        return $this->movementPoints;
    }

    public function setMovementPoints(array $movementPoints): static
    {
        $this->movementPoints = new ProbaCollection($movementPoints);

        return $this;
    }

    public function getHealthPoints(): ProbaCollection
    {
        return $this->healthPoints;
    }

    public function setHealthPoints(array $healthPoints): static
    {
        $this->healthPoints = new ProbaCollection($healthPoints);

        return $this;
    }

    public function getMoralPoints(): ProbaCollection
    {
        return $this->moralPoints;
    }

    public function setMoralPoints(array $moralPoints): static
    {
        $this->moralPoints = new ProbaCollection($moralPoints);

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
        return $this->extraEffects;
    }

    public function setExtraEffects(array $extraEffects): static
    {
        $this->extraEffects = new ProbaCollection($extraEffects);

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
