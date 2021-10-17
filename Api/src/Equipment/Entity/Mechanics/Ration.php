<?php

namespace Mush\Equipment\Entity\Config\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Ration extends EquipmentMechanic
{
    protected string $mechanic = EquipmentMechanicEnum::RATION;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $moralPoints = [0 => 1];
    //  possibilities are stored as key, array value represent the probability to get the key value

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $actionPoints = [0 => 1];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $movementPoints = [0 => 1];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $healthPoints = [0 => 1];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $satiety = null;

    //Store any extra effect the food has as key with the chance to get it as value
    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $extraEffects = [];

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $isPerishable = true;

    public function getActionPoints(): array
    {
        return $this->actionPoints;
    }

    /**
     * @return static
     */
    public function setActionPoints(array $actionPoints): Ration
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }

    public function getMovementPoints(): array
    {
        return $this->movementPoints;
    }

    /**
     * @return static
     */
    public function setMovementPoints(array $movementPoints): Ration
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }

    public function getHealthPoints(): array
    {
        return $this->healthPoints;
    }

    /**
     * @return static
     */
    public function setHealthPoints(array $healthPoints): Ration
    {
        $this->healthPoints = $healthPoints;

        return $this;
    }

    public function getMoralPoints(): array
    {
        return $this->moralPoints;
    }

    /**
     * @return static
     */
    public function setMoralPoints(array $moralPoints): Ration
    {
        $this->moralPoints = $moralPoints;

        return $this;
    }

    public function getSatiety(): ?int
    {
        return $this->satiety;
    }

    /**
     * @return static
     */
    public function setSatiety(?int $satiety): Ration
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function getExtraEffects(): array
    {
        return $this->extraEffects;
    }

    /**
     * @return static
     */
    public function setExtraEffects(array $extraEffects): Ration
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }

    public function isPerishable(): bool
    {
        return $this->isPerishable;
    }

    /**
     * @return static
     */
    public function setIsPerishable(bool $isPerishable): Ration
    {
        $this->isPerishable = $isPerishable;

        return $this;
    }
}
