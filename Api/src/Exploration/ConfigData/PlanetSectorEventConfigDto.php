<?php

declare(strict_types=1);

namespace Mush\Exploration\ConfigData;

use Mush\Exploration\Enum\PlanetSectorEventTagEnum;

/** @codeCoverageIgnore */
final readonly class PlanetSectorEventConfigDto
{
    /**
     * @param array<int, int>   $outputQuantity
     * @param array<mixed, int> $outputTable
     */
    public function __construct(
        public string $name,
        public string $eventName,
        public array $outputQuantity = [],
        public array $outputTable = [],
        public string $tag = PlanetSectorEventTagEnum::NEUTRAL,
    ) {}
}
