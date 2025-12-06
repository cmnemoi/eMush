<?php

declare(strict_types=1);

namespace Mush\Modifier\Dto;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;

final readonly class TriggerEventModifierConfigDto extends EventModifierConfigDto
{
    public function __construct(
        public string $key,
        public ?string $name,
        public string $strategy,
        public string $modifierRange,
        public array $modifierActivationRequirements,
        public string $targetEvent,
        public string $triggeredEvent,
        public array $eventActivationRequirements,
        public array $tagConstraints = [],
        public bool $applyWhenTargeted = false,
        public string $priority = ModifierPriorityEnum::BEFORE_INITIAL_EVENT,
        public string $visibility = VisibilityEnum::PUBLIC,
        public array $targetFilters = [],
    ) {}
}
