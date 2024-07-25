<?php

declare(strict_types=1);

namespace Mush\Skill\Dto;

use Mush\Skill\Enum\SkillName;
use Mush\Status\Enum\SkillPointsEnum;

final readonly class SkillConfigDto
{
    public function __construct(
        public SkillName $name,
        public array $modifierConfigs = [],
        public array $actionConfigs = [],
        public ?string $spawnEquipmentConfig = null,
        public ?SkillPointsEnum $skillPointsConfig = null,
    ) {}
}
