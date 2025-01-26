<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\ModifyMaxDamageWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class ModifyMaxDamageWeaponEffectConfig extends AbstractEventConfig implements QuantityWeaponEffectConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $quantity = 0;

    public function __construct(
        string $name,
        string $eventName,
        int $quantity = 0,
    ) {
        parent::__construct($name, $eventName);
        $this->quantity = $quantity;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function updateFromDto(ModifyMaxDamageWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->quantity = $dto->quantity;
    }
}
