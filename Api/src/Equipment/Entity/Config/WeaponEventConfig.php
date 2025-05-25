<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\WeaponEventConfigDto;
use Mush\Equipment\Enum\WeaponEventType;
use Mush\Game\Entity\AbstractEventConfig;

#[ORM\Entity]
class WeaponEventConfig extends AbstractEventConfig
{
    #[ORM\Column(type: 'string', nullable: false, enumType: WeaponEventType::class, options: ['default' => WeaponEventType::NULL])]
    private WeaponEventType $weaponEventType = WeaponEventType::NULL;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $effectKeys = [];

    public function __construct(
        string $name,
        string $eventName,
        WeaponEventType $eventType,
        array $effectKeys,
    ) {
        parent::__construct($name, $eventName);
        $this->weaponEventType = $eventType;
        $this->effectKeys = $effectKeys;
    }

    public static function fromConfigData(WeaponEventConfigDto $dto): self
    {
        return new self(
            name: $dto->name,
            eventName: $dto->eventName,
            eventType: $dto->eventType,
            effectKeys: $dto->effectKeys,
        );
    }

    public function updateFromDto(WeaponEventConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->weaponEventType = $dto->eventType;
        $this->effectKeys = $dto->effectKeys;
    }

    public function getType(): WeaponEventType
    {
        return $this->weaponEventType;
    }

    public function getEffectKeys(): array
    {
        return $this->effectKeys;
    }
}
