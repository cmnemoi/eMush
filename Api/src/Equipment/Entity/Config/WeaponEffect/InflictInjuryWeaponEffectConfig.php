<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\InflictInjuryWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class InflictInjuryWeaponEffectConfig extends AbstractEventConfig implements BackfireWeaponEffectConfig, RandomWeaponEffectConfig
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $injuryName;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 100])]
    private int $triggerRate = 100;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $toShooter = false;

    public function __construct(
        string $name,
        string $eventName,
        string $injuryName,
        int $triggerRate = 100,
        bool $toShooter = false,
    ) {
        parent::__construct($name, $eventName);
        $this->injuryName = $injuryName;
        $this->triggerRate = $triggerRate;
        $this->toShooter = $toShooter;
    }

    public function getInjuryName(): string
    {
        return $this->injuryName;
    }

    public function getTriggerRate(): int
    {
        return $this->triggerRate;
    }

    public function applyToShooter(): bool
    {
        return $this->toShooter;
    }

    public function updateFromDto(InflictInjuryWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->injuryName = $dto->injuryName;
        $this->triggerRate = $dto->triggerRate;
        $this->toShooter = $dto->toShooter;
    }
}
