<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\InflictRandomInjuryWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class InflictRandomInjuryWeaponEffectConfig extends AbstractEventConfig implements BackfireWeaponEffectConfig, RandomWeaponEffectConfig, QuantityWeaponEffectConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $quantity = 1;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 100])]
    private int $triggerRate = 100;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $toShooter;

    public function __construct(
        string $name,
        string $eventName,
        int $triggerRate = 100,
        int $quantity = 1,
        bool $toShooter = false,
    ) {
        parent::__construct($name, $eventName);
        $this->triggerRate = $triggerRate;
        $this->quantity = $quantity;
        $this->toShooter = $toShooter;
    }

    public function updateFromDto(InflictRandomInjuryWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->triggerRate = $dto->triggerRate;
        $this->quantity = $dto->quantity;
        $this->toShooter = $dto->toShooter;
    }

    public function getTriggerRate(): int
    {
        return $this->triggerRate;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function applyToShooter(): bool
    {
        return $this->toShooter;
    }
}
