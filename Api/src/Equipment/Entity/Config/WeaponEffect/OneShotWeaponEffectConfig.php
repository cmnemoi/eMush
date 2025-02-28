<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\OneShotWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class OneShotWeaponEffectConfig extends AbstractEventConfig implements BackfireWeaponEffectConfig
{
    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $endCause;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $toShooter;

    public function __construct(
        string $name,
        string $eventName,
        string $endCause,
        bool $toShooter = false,
    ) {
        parent::__construct($name, $eventName);
        $this->endCause = $endCause;
        $this->toShooter = $toShooter;
    }

    public static function fromDto(OneShotWeaponEffectConfigDto $dto): self
    {
        return $dto->toEntity();
    }

    public function getEndCause(): string
    {
        return $this->endCause;
    }

    public function applyToShooter(): bool
    {
        return $this->toShooter;
    }

    public function updateFromDto(OneShotWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->endCause = $dto->endCause;
        $this->toShooter = $dto->toShooter;
    }
}
