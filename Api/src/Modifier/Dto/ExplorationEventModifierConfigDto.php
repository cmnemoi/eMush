<?php

declare(strict_types=1);

namespace Mush\Modifier\Dto;

use Mush\Modifier\Enum\ModifierPriorityEnum;

readonly class ExplorationEventModifierConfigDto extends EventModifierConfigDto
{
    public function __construct(
        public string $key,
        public ?string $name,
        public string $strategy,
        public string $modifierRange,
        public array $modifierActivationRequirements,
        public string $targetEvent,
        public bool $applyWhenTargeted = false,
        public string $priority = ModifierPriorityEnum::BEFORE_INITIAL_EVENT,
        public array $tagConstraints = [],
        public string $action = 'remove',
        public string $criteria = 'event_name',
        public ?string $eventToRemove = null,
        public ?string $eventToAdd = null,
        public ?int $weight = null,
    ) {}
}
