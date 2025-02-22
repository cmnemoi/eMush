<?php

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\SplashDamageAllWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class SplashDamageAllWeaponEffectConfig extends AbstractEventConfig
{
    public function __construct(
        string $name,
        string $eventName,
    ) {
        parent::__construct($name, $eventName);
    }

    public function updateFromDto(SplashDamageAllWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
    }
}
