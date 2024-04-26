<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

/**
 * @codeCoverageIgnore
 */
final class ProjectConfigData
{
    public static function getAll(): array
    {
        return [
            [
                'name' => ProjectName::PILGRED,
                'type' => ProjectType::PILGRED,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::FIRE_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::FIREFIGHTER],
            ],
            [
                'name' => ProjectName::DOOR_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::ROBOTICS_EXPERT],
            ],
            [
                'name' => ProjectName::EQUIPMENT_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::TECHNICIAN],
            ],
        ];
    }
}
