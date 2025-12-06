<?php

declare(strict_types=1);

namespace Mush\Modifier\Dto;

use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;

final readonly class VariableEventModifierConfigDto extends EventModifierConfigDto
{
    public function __construct(
        public string $key,
        public ?string $name,
        public string $strategy,
        public string $modifierRange,
        public array $modifierActivationRequirements,
        public string $targetEvent,
        public string $targetVariable,
        public bool $applyWhenTargeted = false,
        public string $priority = ModifierPriorityEnum::BEFORE_INITIAL_EVENT,
        public array $tagConstraints = [],
        public float $delta = 0,
        public string $mode = VariableModifierModeEnum::ADDITIVE,
    ) {}
}
