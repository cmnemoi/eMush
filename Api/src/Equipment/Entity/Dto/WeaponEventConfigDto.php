<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Dto;

use Mush\Equipment\Entity\Config\WeaponEventConfig;
use Mush\Equipment\Enum\WeaponEventType;

final readonly class WeaponEventConfigDto
{
    public function __construct(
        public string $name,
        public string $eventName,
        public WeaponEventType $eventType,
        public array $effectKeys = [],
    ) {}

    public function toEntity(): WeaponEventConfig
    {
        return new WeaponEventConfig($this->name, $this->eventName, $this->eventType, $this->effectKeys);
    }
}
