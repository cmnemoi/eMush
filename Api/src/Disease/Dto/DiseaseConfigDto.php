<?php

declare(strict_types=1);

namespace Mush\Disease\Dto;

final readonly class DiseaseConfigDto
{
    public function __construct(
        public string $key,
        public string $name,
        public string $type,
        public bool $canHealNaturally = true,
        /** @var int[] */
        public array $duration = [1, 4],
        public int $healActionResistance = 1,
        public bool $mushCanHave = false,
        /** @var string[] */
        public array $modifierConfigs = [],
        /** @var string[] */
        public array $removeLower = [],
        public string $eventWhenAppeared = 'none',
    ) {}
}
