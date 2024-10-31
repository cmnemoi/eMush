<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\InflictRandomInjuryWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class InflictRandomInjuryWeaponEffectConfig extends AbstractEventConfig implements BackfireWeaponEffectConfig
{
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $toShooter;

    public function __construct(
        string $name,
        string $eventName,
        bool $toShooter = false,
    ) {
        parent::__construct($name, $eventName);
        $this->toShooter = $toShooter;
    }

    public function updateFromDto(InflictRandomInjuryWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->toShooter = $dto->toShooter;
    }

    public function applyToShooter(): bool
    {
        return $this->toShooter;
    }
}
