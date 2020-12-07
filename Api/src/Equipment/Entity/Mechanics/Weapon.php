<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity()
 */
class Weapon extends Tool
{
    protected string $mechanic = EquipmentMechanicEnum::WEAPON;

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

    protected array $actions = [ActionEnum::ATTACK];

    public function getBaseAccuracy(): int
    {
        return $this->baseAccuracy;
    }

    public function setBaseAccuracy(int $baseAccuracy): Weapon
    {
        $this->baseAccuracy = $baseAccuracy;

        return $this;
    }

    public function getBaseDamageRange(): array
    {
        return $this->baseDamageRange;
    }

    public function setBaseDamageRange(array $baseDamageRange): Weapon
    {
        $this->baseDamageRange = $baseDamageRange;

        return $this;
    }

    public function getBaseInjuryNumber(): array
    {
        return $this->baseInjuryNumber;
    }

    public function setBaseInjuryNumber(array $baseInjuryNumber): Weapon
    {
        $this->baseInjuryNumber = $baseInjuryNumber;

        return $this;
    }

    public function getExpeditionBonus(): int
    {
        return $this->expeditionBonus;
    }

    public function setExpeditionBonus(int $expeditionBonus): Weapon
    {
        $this->expeditionBonus = $expeditionBonus;

        return $this;
    }

    public function getCriticalSucessEvents(): array
    {
        return $this->criticalSucessEvents;
    }

    public function setCriticalSucessEvents(array $criticalSucessEvents): Weapon
    {
        $this->criticalSucessEvents = $criticalSucessEvents;

        return $this;
    }

    public function getCriticalFailEvents(): array
    {
        return $this->baseDamageRange;
    }

    public function setCriticalFailEvents(array $criticalFailEvents): Weapon
    {
        $this->criticalFailEvents = $criticalFailEvents;

        return $this;
    }
}
