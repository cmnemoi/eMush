<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\BreakWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class BreakWeaponEffectConfig extends AbstractEventConfig
{
    public function __construct(
        string $name,
        string $eventName,
    ) {
        parent::__construct($name, $eventName);
    }

    public function updateFromDto(BreakWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
    }
}
