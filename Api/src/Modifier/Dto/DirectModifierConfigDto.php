<?php

declare(strict_types=1);

namespace Mush\Modifier\Dto;

final readonly class DirectModifierConfigDto extends AbstractModifierConfigDto
{
    public function __construct(
        public string $key,
        public ?string $name,
        public string $strategy,
        public string $modifierRange,
        public array $modifierActivationRequirements,
        public string $triggeredEvent,
        public array $eventActivationRequirements,
        public bool $revertOnRemove = false,
        public array $targetFilters = []
    ) {}
}
