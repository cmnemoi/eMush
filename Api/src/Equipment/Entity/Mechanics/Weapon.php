<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Weapon extends Tool
{
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $baseAccuracy = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $baseDamageRange = [0 => 0];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $baseInjuryNumber = [0 => 0];

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $expeditionBonus = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $criticalSucessEvents = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $criticalFailEvents = [];

    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::WEAPON;
    }

    public function getBaseAccuracy(): int
    {
        return $this->baseAccuracy;
    }

    public function setBaseAccuracy(int $baseAccuracy): static
    {
        $this->baseAccuracy = $baseAccuracy;

        return $this;
    }

    public function getBaseDamageRange(): array
    {
        return $this->baseDamageRange;
    }

    public function setBaseDamageRange(array $baseDamageRange): static
    {
        $this->baseDamageRange = $baseDamageRange;

        return $this;
    }

    public function getBaseInjuryNumber(): array
    {
        return $this->baseInjuryNumber;
    }

    public function setBaseInjuryNumber(array $baseInjuryNumber): static
    {
        $this->baseInjuryNumber = $baseInjuryNumber;

        return $this;
    }

    public function getExpeditionBonus(): int
    {
        return $this->expeditionBonus;
    }

    public function setExpeditionBonus(int $expeditionBonus): static
    {
        $this->expeditionBonus = $expeditionBonus;

        return $this;
    }

    public function getCriticalSucessEvents(): array
    {
        return $this->criticalSucessEvents;
    }

    public function setCriticalSucessEvents(array $criticalSucessEvents): static
    {
        $this->criticalSucessEvents = $criticalSucessEvents;

        return $this;
    }

    public function getCriticalFailEvents(): array
    {
        return $this->criticalFailEvents;
    }

    public function setCriticalFailEvents(array $criticalFailEvents): static
    {
        $this->criticalFailEvents = $criticalFailEvents;

        return $this;
    }
}
