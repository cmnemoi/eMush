<?php

declare(strict_types=1);

namespace Mush\Skill\Dto;

use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\SkillPointsEnum;

final readonly class SkillConfigDto
{
    public function __construct(
        public SkillEnum $name,
        public array $modifierConfigs = [],
        public array $actionConfigs = [],
        public ?string $spawnEquipmentConfig = null,
        public ?SkillPointsEnum $skillPointsConfig = null,
    ) {}
}
