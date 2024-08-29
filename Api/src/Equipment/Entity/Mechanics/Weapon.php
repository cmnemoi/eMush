<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
class Weapon extends Tool
{
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $baseAccuracy = 0;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $baseDamageRange;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $expeditionBonus = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $criticalSuccessRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $criticalFailRate = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $oneShotRate = 0;

    public function __construct()
    {
        parent::__construct();
        $this->baseDamageRange = [];
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::WEAPON;

        return $mechanics;
    }

    public function getBaseAccuracy(): int
    {
        return $this->baseAccuracy;
    }

    public function setBaseAccuracy(int $baseAccuracy): self
    {
        $this->baseAccuracy = $baseAccuracy;

        return $this;
    }

    public function getBaseDamageRange(): ProbaCollection
    {
        return new ProbaCollection($this->baseDamageRange);
    }

    public function setBaseDamageRange(array $baseDamageRange): self
    {
        $this->baseDamageRange = $baseDamageRange;

        return $this;
    }

    public function getExpeditionBonus(): int
    {
        return $this->expeditionBonus;
    }

    public function setExpeditionBonus(int $expeditionBonus): self
    {
        $this->expeditionBonus = $expeditionBonus;

        return $this;
    }

    public function getCriticalSuccessRate(): int
    {
        return $this->criticalSuccessRate;
    }

    public function setCriticalSuccessRate(int $criticalSuccessRate): self
    {
        $this->criticalSuccessRate = $criticalSuccessRate;

        return $this;
    }

    public function getCriticalFailRate(): int
    {
        return $this->criticalFailRate;
    }

    public function setCriticalFailRate(int $criticalFailRate): self
    {
        $this->criticalFailRate = $criticalFailRate;

        return $this;
    }

    public function getOneShotRate(): int
    {
        return $this->oneShotRate;
    }

    public function setOneShotRate(int $oneShotRate): self
    {
        $this->oneShotRate = $oneShotRate;

        return $this;
    }
}
