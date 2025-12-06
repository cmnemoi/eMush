<?php

declare(strict_types=1);

namespace Mush\Modifier\Dto;

readonly class AbstractModifierConfigDto
{
    public function __construct(
        public string $key,
        public ?string $name,
        public string $strategy,
        public string $modifierRange,
        public array $modifierActivationRequirements,
    ) {}
}
