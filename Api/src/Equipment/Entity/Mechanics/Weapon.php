<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Weapon extends Tool
{
    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $baseAccuracy = 0;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $baseDamageRange = [0 => 0];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $baseInjuryNumber = [0 => 0];

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $expeditionBonus = 0;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $criticalSucessEvents = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $criticalFailEvents = [];

    /**
     * Weapon constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::WEAPON;
    }

    public function getBaseAccuracy(): int
    {
        return $this->baseAccuracy;
    }

    /**
     * @return static
     */
    public function setBaseAccuracy(int $baseAccuracy): self
    {
        $this->baseAccuracy = $baseAccuracy;

        return $this;
    }

    public function getBaseDamageRange(): array
    {
        return $this->baseDamageRange;
    }

    /**
     * @return static
     */
    public function setBaseDamageRange(array $baseDamageRange): self
    {
        $this->baseDamageRange = $baseDamageRange;

        return $this;
    }

    public function getBaseInjuryNumber(): array
    {
        return $this->baseInjuryNumber;
    }

    /**
     * @return static
     */
    public function setBaseInjuryNumber(array $baseInjuryNumber): self
    {
        $this->baseInjuryNumber = $baseInjuryNumber;

        return $this;
    }

    public function getExpeditionBonus(): int
    {
        return $this->expeditionBonus;
    }

    /**
     * @return static
     */
    public function setExpeditionBonus(int $expeditionBonus): self
    {
        $this->expeditionBonus = $expeditionBonus;

        return $this;
    }

    public function getCriticalSucessEvents(): array
    {
        return $this->criticalSucessEvents;
    }

    /**
     * @return static
     */
    public function setCriticalSucessEvents(array $criticalSucessEvents): self
    {
        $this->criticalSucessEvents = $criticalSucessEvents;

        return $this;
    }

    public function getCriticalFailEvents(): array
    {
        return $this->criticalFailEvents;
    }

    /**
     * @return static
     */
    public function setCriticalFailEvents(array $criticalFailEvents): self
    {
        $this->criticalFailEvents = $criticalFailEvents;

        return $this;
    }
}
