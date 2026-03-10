<?php

declare(strict_types=1);

namespace Mush\Skill\Dto;

use Mush\Skill\Enum\SkillEnum;

final readonly class SkillConfigDto
{
    public function __construct(
        public SkillEnum $name,
        public array $modifierConfigs = [],
        public array $actionConfigs = [],
        public array $statusConfigs = [],
        public ?string $spawnEquipmentConfig = null,
    ) {}
}
