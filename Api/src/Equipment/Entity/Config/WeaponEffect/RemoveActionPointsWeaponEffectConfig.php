<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\RemoveActionPointsWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class RemoveActionPointsWeaponEffectConfig extends AbstractEventConfig implements BackfireWeaponEffectConfig, QuantityWeaponEffectConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $quantity = 0;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $toShooter = false;

    public function __construct(
        string $name,
        string $eventName,
        int $quantity = 0,
        bool $toShooter = false
    ) {
        parent::__construct($name, $eventName);
        $this->quantity = $quantity;
        $this->toShooter = $toShooter;
    }

    public function applyToShooter(): bool
    {
        return $this->toShooter;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function updateFromDto(RemoveActionPointsWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->quantity = $dto->quantity;
        $this->toShooter = $dto->toShooter;
    }
}
