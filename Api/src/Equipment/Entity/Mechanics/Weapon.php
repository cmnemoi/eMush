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

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $expeditionBonus = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $criticalSucessRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $criticalFailRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $oneShotRate = 0;

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

    public function getExpeditionBonus(): int
    {
        return $this->expeditionBonus;
    }

    public function setExpeditionBonus(int $expeditionBonus): static
    {
        $this->expeditionBonus = $expeditionBonus;

        return $this;
    }

    public function getCriticalSucessRate(): int
    {
        return $this->criticalSucessRate;
    }

    public function setCriticalSucessRate(int $criticalSucessRate): static
    {
        $this->criticalSucessRate = $criticalSucessRate;

        return $this;
    }

    public function getCriticalFailRate(): int
    {
        return $this->criticalFailRate;
    }

    public function setCriticalFailRate(int $criticalFailRate): static
    {
        $this->criticalFailRate = $criticalFailRate;

        return $this;
    }

    public function getOneShotRate(): int
    {
        return $this->oneShotRate;
    }

    public function setOneShotRate(int $oneShotRate): static
    {
        $this->oneShotRate = $oneShotRate;

        return $this;
    }
}
