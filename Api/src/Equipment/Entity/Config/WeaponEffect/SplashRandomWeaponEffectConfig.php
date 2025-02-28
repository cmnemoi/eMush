<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config\WeaponEffect;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEffect\SplashRandomWeaponEffectConfigDto;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class SplashRandomWeaponEffectConfig extends AbstractEventConfig implements QuantityWeaponEffectConfig, RandomWeaponEffectConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $quantity = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 100])]
    private int $triggerRate = 100;

    public function __construct(
        string $name,
        string $eventName,
        int $triggerRate = 100,
        int $quantity = 0,
    ) {
        parent::__construct($name, $eventName);
        $this->triggerRate = $triggerRate;
        $this->quantity = $quantity;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTriggerRate(): int
    {
        return $this->triggerRate;
    }

    public function updateFromDto(SplashRandomWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->triggerRate = $dto->triggerRate;
        $this->quantity = $dto->quantity;
    }
}
